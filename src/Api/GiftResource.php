<?php
namespace WaldorfshopCheckoutGeschenk\Api;
use IO\Api\ApiResource;
use Plenty\Plugin\Http\Response;
use WaldorfshopCheckoutGeschenk\Services\GiftService;
class GiftResource extends ApiResource
{
    public function index(): Response { return $this->response->create(pluginApp(GiftService::class)->state(), 200); }
    public function store(): Response {
        $selected=$this->request->get('selected');
        if (!is_bool($selected)) return $this->response->create(['error'=>'Ungültige Auswahl.'],422);
        try {
            return $this->response->create(pluginApp(GiftService::class)->select($selected,(int)$this->request->get('basketId',0)),200);
        } catch (\RuntimeException $e) {
            return $this->response->create(['error'=>$e->getMessage()],409);
        }
    }
}
