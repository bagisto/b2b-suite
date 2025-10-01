<?php

namespace Webkul\B2BSuite\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
            'b2b_suite_acl'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/bagisto-vite.php',
            'bagisto-vite.viters'
        );

        $this->mergeAuthConfigs();

        $this->registerServices();

        $this->registerFacades();
    }

    /**
     * Merge Auth Configs.
     */
    public function mergeAuthConfigs(): void
    {
        foreach (['guards', 'providers', 'passwords'] as $key) {
            $this->mergeConfigFrom(
                dirname(__DIR__).'/Config/company/auth/'.$key.'.php',
                'auth.'.$key
            );
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__.'/../Http/helpers.php';

        Route::middleware('web')->group(__DIR__.'/../Routes/web.php');

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'b2b_suite');

        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'b2b_suite');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'b2b_suite');

        $this->registerCommands();

        $this->registerProviders();

        $this->publishAssets();

        if (
            Schema::hasTable('core_config')
            && (bool) core()->getConfigData('b2b_suite.general.settings.active')
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
                \Webkul\B2BSuite\Console\Commands\InstallB2BSuite::class,
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
            __DIR__.'/../../publishable/build' => public_path('themes/b2b-suite/build'),
        ], 'public');

        $this->publishes([
            __DIR__.'/../../publishable/storage' => storage_path('app/public'),
        ]);

        $this->publishes([
            __DIR__.'/../Resources/views/admin/customers/customers' => resource_path('admin-themes/default/views/customers/customers'),
        ]);
    }

    /**
     * Register services.
     */
    protected function registerServices(): void
    {
        $this->app->bind(
            \Webkul\B2BSuite\Contracts\Company::class,
            \Webkul\B2BSuite\Repositories\CompanyRepository::class
        );

        $this->app->bind(
            \Webkul\B2BSuite\Contracts\CompanyFlat::class,
            \Webkul\B2BSuite\Repositories\CompanyFlatRepository::class
        );
    }

    /**
     * Register facades.
     */
    protected function registerFacades(): void
    {
        // $this->app->singleton('b2b', function () {
        //     return new \Webkul\B2BSuite\Helpers\B2BHelper;
        // });
    }
}
