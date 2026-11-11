<?php
namespace WaldorfshopCheckoutGeschenk\Providers;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;
use Plenty\Modules\Order\Injection\Contracts\OrderInjectionServiceContract;
use WaldorfshopCheckoutGeschenk\Services\GiftService;
class GiftProvider extends ServiceProvider
{
    public function register() { $this->getApplication()->register(GiftRoutes::class); }
    public function boot() {
        pluginApp(OrderInjectionServiceContract::class)->registerOnCreateBeforeValidation([1], GiftService::class);
    }
    public function call(Twig $twig) {
        return pluginApp(GiftService::class)->enabled() ? $twig->render('WaldorfshopCheckoutGeschenk::Checkout.Script') : '';
    }
}
