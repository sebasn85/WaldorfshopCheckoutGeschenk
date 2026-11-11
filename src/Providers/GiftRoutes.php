<?php
namespace WaldorfshopCheckoutGeschenk\Providers;
use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\ApiRouter;
class GiftRoutes extends RouteServiceProvider
{
    public function map(ApiRouter $api) {
        $api->version(['v1'], ['namespace'=>'WaldorfshopCheckoutGeschenk\Api'], function(ApiRouter $api) {
            $api->get('waldorfshop/checkout-gift','GiftResource@index');
            $api->post('waldorfshop/checkout-gift','GiftResource@store');
        });
    }
}
