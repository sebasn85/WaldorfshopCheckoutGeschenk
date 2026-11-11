<?php
namespace WaldorfshopCheckoutGeschenk\Providers;
use Plenty\Plugin\Templates\Twig;
use WaldorfshopCheckoutGeschenk\Services\GiftService;
class WidgetProvider
{
    public function call(Twig $twig) {
        return pluginApp(GiftService::class)->enabled() ? $twig->render('WaldorfshopCheckoutGeschenk::Checkout.Widget') : '';
    }
}
