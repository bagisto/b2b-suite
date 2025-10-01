<?php

namespace Webkul\B2BSuite\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Webkul\B2BSuite\Listeners\Company;
use Webkul\B2BSuite\Listeners\Order;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /**
         * Customer/Company related events.
         */
        'customer.registration.after' => [
            [Company::class, 'afterUpdate'],
        ],
        'customer.update.after' => [
            [Company::class, 'afterUpdate'],
        ],

        /**
         * Sales related events.
         */
        'checkout.order.save.after' => [
            [Order::class, 'afterCreated'],
        ],

        /**
         * Invoice & Shipment related events.
         */
        'sales.invoice.save.after' => [
            [Order::class, 'afterUpdated'],
        ],
        'sales.shipment.save.after' => [
            [Order::class, 'afterUpdated'],
        ],
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $templates = [
            [
                'event'    => 'bagisto.shop.layout.head.after',
                'template' => 'b2b_suite::components.layouts.scripts',
            ],
            [
                'event'    => 'bagisto.shop.checkout.cart.summary.proceed_to_checkout.before',
                'template' => 'b2b_suite::shop.checkout.cart.request-quote-button',
            ],
            [
                'event'    => 'bagisto.shop.products.view.additional_actions.before',
                'template' => 'b2b_suite::shop.customers.account.requisitions.list-modal',
            ],
            [
                'event'    => 'bagisto.shop.checkout.cart.continue_shopping.before',
                'template' => 'b2b_suite::shop.customers.account.requisitions.list-modal',
            ],
            [
                // 'event'    => 'bagisto.shop.components.products.card.compare_option.after',
                // 'template' => 'b2b_suite::shop.customers.account.requisitions.list-modal',
            ],
        ];

        /**
         * Checks if the `core_config` table exists.
         * This is necessary because if someone installs Bagisto and the B2B Suite module simultaneously,
         * it may cause an error due to the `core_config` table not being available during the installation process.
         */
        if (
            Schema::hasTable('core_config')
            && core()->getConfigData('b2b_suite.general.settings.active')
        ) {
            foreach ($templates as $template) {
                Event::listen(current($template), fn ($e) => $e->addTemplate(end($template)));
            }
        }
    }
}
