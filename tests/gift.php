<?php
namespace Plenty\Modules\Order\Injection\Contracts { interface OrderInjectionOnCreateContract { public function handle(array $data):array; } }
namespace {
$language='de';$services=[];$config=['enabled'=>'true','threshold'=>'59','variationId'=>'37374'];$choice=null;$uri='/place-order';$salable=true;$images=['variation'=>[['urlPreview'=>'https://example.invalid/variant.jpg','position'=>1]],'all'=>[['urlPreview'=>'https://example.invalid/item.jpg','position'=>0]]];
$basket=(object)['id'=>7,'orderId'=>0,'currency'=>'EUR','itemSum'=>59,'couponDiscount'=>0,'referrerId'=>1,'shippingProfileId'=>6];
$rows=[['variationId'=>123,'quantity'=>2]];
function pluginApp($name){global $services;return $services[$name];}
$services['Plenty\\Plugin\\ConfigRepository']=new class { function get($k,$d){global $config;return $config[substr($k,strrpos($k,'.')+1)]??$d;} };
$services['Plenty\\Modules\\Basket\\Contracts\\BasketRepositoryContract']=new class {function load(){global $basket;return $basket;} };
$services['Plenty\\Modules\\Basket\\Contracts\\BasketItemRepositoryContract']=new class {function all(){global $rows;return $rows;} };
$services['Plenty\\Modules\\Webshop\\Contracts\\SessionStorageRepositoryContract']=new class {function getSessionValue($k){global $choice;return $choice;}function setSessionValue($k,$v){global $choice;$choice=$v;} };
$services['Plenty\\Plugin\\Http\\Request']=new class {function getRequestUri(){global $uri;return $uri;} };
$services['IO\\Services\\ItemService']=new class {function getVariation($id){global $salable,$images;return ['documents'=>[['data'=>['variation'=>['id'=>$id,'vatId'=>1],'filter'=>['isSalable'=>$salable],'images'=>$images]]]];}function getVariationIsSalable($id){global $salable;return $salable;} };
$services['IO\\Extensions\\Filters\\ItemNameFilter']=new class {function itemName($v){return 'Testgeschenk';} };
$services['Plenty\\Modules\\Webshop\\Contracts\\LocalizationRepositoryContract']=new class {function getLanguage(){global $language;return $language;} };
require '/project/src/Services/GiftService.php';
$s=new \WaldorfshopCheckoutGeschenk\Services\GiftService;
$n=0;function check($v,$label){global $n;if(!$v)throw new \Exception('FAIL '.$label);++$n;}
$order=['typeId'=>1,'orderItems'=>[['typeId'=>1,'itemVariationId'=>123,'quantity'=>2,'amounts'=>[['priceOriginalGross'=>29.5]]]]];
check($s->state()['image']==='https://example.invalid/variant.jpg','variation image preferred');
$images['variation']=[];check($s->state()['image']==='https://example.invalid/item.jpg','article image fallback');
$images['all']=[];check($s->state()['image']==='','missing image safe');
$images['all']=[['urlPreview'=>'https://example.invalid/later.jpg','position'=>3],['urlPreview'=>'https://example.invalid/first.jpg','position'=>1]];check($s->state()['image']==='https://example.invalid/first.jpg','first image by position');
$images['all']=[['urlPreview'=>'javascript:alert(1)','position'=>0]];check($s->state()['image']==='','invalid image URL rejected');
check(!$s->state()['selected'],'default unchecked');check($s->state()['eligible'],'59 qualifies');
check($s->handle($order)===$order,'no unsolicited gift');
$s->select(true,7);$result=$s->handle($order);check(count($result['orderItems'])===2,'one gift');check($result['orderItems'][1]['amounts'][0]['priceOriginalGross']===0.0,'gross zero');check($result['orderItems'][1]['amounts'][0]['priceOriginalNet']===0.0,'net zero');check($result['orderItems'][1]['quantity']===1,'quantity one');check($result['orderItems'][0]===$order['orderItems'][0],'paid items untouched');check($s->handle($result)===$result,'idempotent');check(count($rows)===1,'no basket mutation');
$s->select(false,7);check($s->handle($order)===$order,'deselection');
$basket->itemSum=58.99;check(!$s->state()['eligible'],'58.99 fails');
$basket->itemSum=60;$basket->couponDiscount=2;check(!$s->state()['eligible'],'coupon excluded');$basket->couponDiscount=0;
$s->select(true,7);$basket->itemSum=58;check(!$s->state()['selected'],'below removes consent');$basket->itemSum=60;check(!$s->state()['selected'],'no reoptin');
$s->select(true,7);$config['variationId']='99';check(!$s->state()['selected'],'changed gift invalidates');$config['variationId']='37374';
$s->select(true,7);$config['enabled']='false';check($s->handle($order)===$order,'disabled no injection');$s->state();$config['enabled']='true';check(!$s->state()['selected'],'disabled consent cleared');
$s->select(true,7);$uri='/rest/orders';check($s->handle($order)===$order,'backend orders excluded');$uri='/en/place-order/';check(count($s->handle($order)['orderItems'])===2,'localized checkout');
$other=$order;$other['orderItems'][0]['itemVariationId']=999;check($s->handle($other)===$other,'unrelated order excluded');
$basket->id=8;check(!$s->state()['selected'],'new basket no consent');
$basket->currency='USD';check(!$s->state()['eligible'],'EUR only');$basket->currency='EUR';$salable=false;check(!$s->state()['eligible'],'unavailable hidden');$salable=true;
$rows[]=['variationId'=>37374,'quantity'=>1];$order['orderItems'][]=['typeId'=>1,'itemVariationId'=>37374,'quantity'=>1,'amounts'=>[['priceOriginalGross'=>5]]];$s->select(true,8);$result=$s->handle($order);check(count($result['orderItems'])===3,'paid copy plus gift');check($result['orderItems'][1]['amounts'][0]['priceOriginalGross']===5,'paid copy keeps price');
try{$s->select(true,7);check(false,'reject stale basket');}catch(\RuntimeException $e){check(true,'reject stale basket');}
$language='en';check(!$s->state()['eligible'],'English excluded by default');check($s->handle($order)===$order,'language blocks order gift');
try{$s->select(true,8);check(false,'forbidden language optin');}catch(\RuntimeException $e){check(true,'forbidden language optin');}
$language='de';check(!$s->state()['selected'],'language switch clears consent');
foreach(['en','fr','it','es','nl'] as $lang){$language=$lang;check(!$s->state()['eligible'],$lang.' hidden');$config['language_'.$lang]='true';check($s->state()['eligible'],$lang.' configurable');$config['language_'.$lang]='false';}
$language='de';$s->select(true,8);$config['language_en']='true';$language='en';check(!$s->state()['selected'],'consent bound to language');$s->select(true,8);check(count($s->handle($order)['orderItems'])===3,'enabled English receives gift');
$language='de';$config['language_de']='false';check(!$s->state()['eligible'],'no selected languages hides gift');$language='zz';check(!$s->state()['eligible'],'unknown language denied');
foreach (glob('/project/src/*/*.php') as $file) token_get_all(file_get_contents($file),TOKEN_PARSE);
echo "$n checks passed; all PHP parsed.\n";
}
