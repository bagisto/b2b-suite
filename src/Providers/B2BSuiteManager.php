<?php

namespace Webkul\B2BSuite\Providers;

use Illuminate\Foundation\Application;
use Webkul\B2BSuite\Http\Controllers\Shop\API\CartController as B2BCartController;
use Webkul\B2BSuite\Http\Controllers\Shop\Customer\CustomerController as ShopCustomerController;
use Webkul\B2BSuite\Http\Controllers\Shop\Customer\RegistrationController;
use Webkul\B2BSuite\Models\Customer;
use Webkul\B2BSuite\Repositories\CategoryRepository as B2BCategoryRepository;
use Webkul\B2BSuite\Repositories\ProductRepository as B2BProductRepository;
use Webkul\Category\Repositories\CategoryRepository as BaseCategoryRepository;
use Webkul\Customer\Contracts\Customer as CustomerContract;
use Webkul\Product\Repositories\ProductRepository as BaseProductRepository;
use Webkul\Shop\Http\Controllers\API\CartController as BaseCartController;
use Webkul\Shop\Http\Controllers\Customer\CustomerController as BaseShopCustomerController;
use Webkul\Shop\Http\Controllers\Customer\RegistrationController as BaseRegistrationController;

final class B2BSuiteManager
{
    /**
     * Constructor to bind classes to the container.
     *
     * @return void
     */
    public function __construct(private Application $app)
    {
        $this->registerModels();

        $this->registerControllers();

        $this->registerRepositories();
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
        $this->app->bind(BaseRegistrationController::class, RegistrationController::class);

        $this->app->bind(BaseShopCustomerController::class, ShopCustomerController::class);

        $this->app->bind(BaseCartController::class, B2BCartController::class);
    }

    /**
     * Register the repositories.
     */
    private function registerRepositories(): void
    {
        /**
         * Enforces company-catalog allowlist visibility on the storefront. The
         * override is inert for guests, admins, and unassigned customers.
         */
        $this->app->bind(BaseProductRepository::class, B2BProductRepository::class);

        $this->app->bind(BaseCategoryRepository::class, B2BCategoryRepository::class);
    }
}
