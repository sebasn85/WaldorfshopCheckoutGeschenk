<?php
namespace WaldorfshopCheckoutGeschenk\Services;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;
use Plenty\Modules\Basket\Contracts\BasketRepositoryContract;
use Plenty\Modules\Basket\Contracts\BasketItemRepositoryContract;
use Plenty\Modules\Webshop\Contracts\SessionStorageRepositoryContract;
use Plenty\Modules\Order\Injection\Contracts\OrderInjectionOnCreateContract;
use IO\Services\ItemService;
use IO\Extensions\Filters\ItemNameFilter;
/** Session opt-in; no basket mutations. Native order creation validates and calculates the free line. */
class GiftService implements OrderInjectionOnCreateContract
{
    const KEY='WaldorfshopCheckoutGeschenk.choice.v1';
    const MARKER='Gratisgeschenk';
    private function setting($key,$default='') { return pluginApp(ConfigRepository::class)->get('WaldorfshopCheckoutGeschenk.gift.'.$key,$default); }
    public function enabled(): bool { return (string)$this->setting('enabled','false')==='true'; }
    private function cents($value): int { return (int)round((float)str_replace(',','.',(string)$value)*100); }
    private function session() { return pluginApp(SessionStorageRepositoryContract::class); }
    private function basket() { return pluginApp(BasketRepositoryContract::class)->load(); }
    private function variation(): array {
        $id=(int)$this->setting('variationId','0');
        if ($id<=0) return [];
        $service=pluginApp(ItemService::class);
        $result=$service->getVariation($id);
        $data=$result['documents'][0]['data'] ?? [];
        if ((int)($data['variation']['id'] ?? 0)!==$id || !$service->getVariationIsSalable($id)) return [];
        return $data;
    }
    public function state(): array {
        $basket=$this->basket();
        $id=(int)$this->setting('variationId','0');
        $threshold=$this->cents($this->setting('threshold','59'));
        $value=$basket ? $this->cents($basket->itemSum)-max(0,$this->cents($basket->couponDiscount)) : 0;
        $eligible=$this->enabled() && $basket && (int)$basket->id>0 && !(int)$basket->orderId && $basket->currency==='EUR' && $id>0 && $threshold>0 && $value>=$threshold;
        $variation=$eligible ? $this->variation() : [];
        $eligible=$eligible && !empty($variation);
        $choice=$this->session()->getSessionValue(self::KEY);
        $selected=$eligible && is_array($choice) && ($choice['selected'] ?? false)===true && (int)($choice['basketId'] ?? 0)===(int)$basket->id && (int)($choice['variationId'] ?? 0)===$id && (int)($choice['threshold'] ?? 0)===$threshold;
        if (!$selected && is_array($choice)) $this->session()->setSessionValue(self::KEY,null);
        return ['enabled'=>$this->enabled(),'eligible'=>(bool)$eligible,'selected'=>(bool)$selected,'basketId'=>$basket ? (int)$basket->id : 0,'variationId'=>$id,'threshold'=>$threshold/100,'qualifyingValue'=>$value/100,'name'=>$variation ? pluginApp(ItemNameFilter::class)->itemName($variation) : '', 'price'=>0,'quantity'=>1];
    }
    public function select(bool $selected,int $basketId): array {
        $state=$this->state();
        if ($basketId<=0 || $state['basketId']!==$basketId) throw new \RuntimeException('Der Warenkorb hat sich geändert. Bitte lade die Kasse neu.');
        if ($selected && !$state['eligible']) throw new \RuntimeException('Das Geschenk ist aktuell nicht verfügbar.');
        $this->session()->setSessionValue(self::KEY,$selected ? ['selected'=>true,'basketId'=>$basketId,'variationId'=>$state['variationId'],'threshold'=>$this->cents($state['threshold'])] : null);
        return $this->state();
    }
    private function itemMap(array $items,string $key): array {
        $map=[];
        foreach ($items as $item) {
            $row=is_array($item) ? $item : $item->toArray();
            $id=(int)($row[$key] ?? 0);
            if ($id>0) $map[$id]=($map[$id] ?? 0)+(float)($row['quantity'] ?? 0);
        }
        ksort($map); return $map;
    }
    public function handle(array $data): array {
        // Only the normal storefront checkout may consume session consent.
        $path=parse_url(pluginApp(Request::class)->getRequestUri(),PHP_URL_PATH);
        if (!preg_match('~/(?:[a-z]{2}/)?place-order/?$~',(string)$path) || (int)($data['typeId'] ?? 0)!==1 || !$this->enabled()) return $data;
        $state=$this->state();
        if (!$state['selected']) return $data;
        $basket=$this->basket();
        $basketItems=pluginApp(BasketItemRepositoryContract::class)->all();
        $orderItems=$data['orderItems'] ?? [];
        // Never add to unrelated orders generated within the same request.
        if (!$basketItems || $this->itemMap($basketItems,'variationId')!==$this->itemMap($orderItems,'itemVariationId')) return $data;
        $variation=$this->variation();
        if (!$variation) throw new \RuntimeException('Das gewählte Geschenk ist nicht mehr verfügbar. Bitte prüfe die Auswahl in der Kasse.');
        $data['orderItems'][]=[
            'typeId'=>1, 'itemVariationId'=>$state['variationId'], 'quantity'=>1,
            'orderItemName'=>$state['name'].' ('.self::MARKER.')',
            'referrerId'=>(float)$basket->referrerId,
            'shippingProfileId'=>(int)$basket->shippingProfileId,
            'vatField'=>(int)($variation['variation']['vatId'] ?? 0),
            'amounts'=>[['currency'=>'EUR','priceOriginalGross'=>0.0,'priceOriginalNet'=>0.0,'priceGross'=>0.0,'priceNet'=>0.0,'surcharge'=>0.0,'discount'=>0.0,'isPercentage'=>true]]
        ];
        return $data;
    }
}
