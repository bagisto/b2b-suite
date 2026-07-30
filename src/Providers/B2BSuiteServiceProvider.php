<?php

namespace Webkul\B2BSuite\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Webkul\B2BSuite\Console\Commands\InstallB2BSuite;
use Webkul\B2BSuite\Console\Commands\SeedDemoData;
use Webkul\B2BSuite\Contracts\CompanyFlat;
use Webkul\B2BSuite\Http\Middleware\CustomerBouncerMiddleware;
use Webkul\B2BSuite\Http\Middleware\EnsurePayByCreditWithinLimit;
use Webkul\B2BSuite\Menu as B2BMenu;
use Webkul\B2BSuite\Repositories\CompanyFlatRepository;
use Webkul\Core\Menu as CoreMenu;

class B2BSuiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/admin/system.php',
            'core'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/admin/acl.php', 'acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/shop/acl.php',
            'b2b_acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/payment-methods.php',
            'payment_methods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/bagisto-vite.php',
            'bagisto-vite.viters'
        );

        $this->registerServices();

        $this->app->bind(CoreMenu::class, B2BMenu::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__.'/../Http/helpers.php';

        if (core()->getConfigData('b2b.general.settings.active')) {
            Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

            require __DIR__.'/../Routes/breadcrumbs.php';

            config([
                'bagisto.tracked_modules' => array_values(array_unique(array_merge(
                    config('bagisto.tracked_modules', []),
                    ['bagisto/b2b-suite']
                ))),
            ]);
        }

        Route::aliasMiddleware('customer_bouncer', CustomerBouncerMiddleware::class);

        /**
         * Guard checkout so a "Pay By Credit" order can never exceed the company's available
         * credit (the method is shown but the order is rejected when over the limit).
         */
        Route::pushMiddlewareToGroup('shop', EnsurePayByCreditWithinLimit::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'b2b');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'b2b');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'b2b');

        $this->registerCommands();

        $this->registerProviders();

        $this->publishAssets();

        if (
            Schema::hasTable('core_config')
            && (bool) core()->getConfigData('b2b.general.settings.active')
        ) {
            $this->mergeConfigFrom(
                dirname(__DIR__).'/Config/admin/menu.php',
                'menu.admin'
            );

            $this->mergeConfigFrom(
                dirname(__DIR__).'/Config/shop/menu.php',
                'menu.customer'
            );
        }

        /**
         * Calls the `B2BSuiteManager` class to bind the classes to the container.
         */
        new B2BSuiteManager($this->app);
    }

    /**
     * Register console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallB2BSuite::class,
                SeedDemoData::class,
            ]);
        }
    }

    /**
     * Register the providers.
     */
    protected function registerProviders(): void
    {
        $this->app->register(ModuleServiceProvider::class);

        $this->app->register(EventServiceProvider::class);
    }

    /**
     * Publish the assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../../publishables/public/themes/b2b-suite' => public_path('themes/b2b-suite'),

            __DIR__.'/../../publishables/resources/vendor' => resource_path('views/vendor'),

            __DIR__.'/../../publishables/storage' => storage_path('app/public'),
        ]);
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        $this->app->bind(
            CompanyFlat::class,
            CompanyFlatRepository::class
        );
    }
}
