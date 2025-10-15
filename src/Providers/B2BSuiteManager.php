<?php

namespace Webkul\B2BSuite\Providers;

use Illuminate\Foundation\Application;
use Webkul\Admin\Http\Controllers\Customers\CustomerController as BaseCustomerController;
use Webkul\B2BSuite\Http\Controllers\Admin\CustomerController;
use Webkul\B2BSuite\Http\Controllers\Shop\API\CartController as B2BCartController;
use Webkul\B2BSuite\Http\Controllers\Shop\Customer\CustomerController as ShopCustomerController;
use Webkul\B2BSuite\Http\Controllers\Shop\Customer\RegistrationController;
use Webkul\B2BSuite\Models\Customer;
use Webkul\Customer\Contracts\Customer as CustomerContract;
use Webkul\Shop\Http\Controllers\API\CartController as BaseCartController;
use Webkul\Shop\Http\Controllers\Customer\CustomerController as BaseShopCustomerController;
use Webkul\Shop\Http\Controllers\Customer\RegistrationController as BaseRegistrationController;

final class B2BSuiteManager
{
    /**
     * Constructor to bind classes to the container
     *
     * @return void
     */
    public function __construct(private Application $app)
    {
        $this->registerModels();

        $this->registerControllers();

    }

    /**
     * Register the marketplace models.
     */
    private function registerModels(): void
    {
        $this->app->concord->registerModel(CustomerContract::class, Customer::class);
    }

    /**
     * Register the marketplace controllers.
     */
    private function registerControllers(): void
    {
        $this->app->bind(BaseCustomerController::class, CustomerController::class);

        $this->app->bind(BaseRegistrationController::class, RegistrationController::class);

        $this->app->bind(BaseShopCustomerController::class, ShopCustomerController::class);

        $this->app->bind(BaseCartController::class, B2BCartController::class);
    }
}
