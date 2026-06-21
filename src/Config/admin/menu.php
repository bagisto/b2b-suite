<?php

return [
    [
        'key' => 'b2b',
        'name' => 'b2b::app.admin.layouts.b2b',
        'route' => 'admin.b2b.companies.index',
        'sort' => 5,
        'icon' => 'icon-store',
    ],

    /**
     * Company management.
     */
    [
        'key' => 'b2b.companies',
        'name' => 'b2b::app.admin.layouts.companies',
        'route' => 'admin.b2b.companies.index',
        'sort' => 1,
        'icon' => '',
    ], [
        'key' => 'b2b.attributes',
        'name' => 'b2b::app.admin.layouts.company-attributes',
        'route' => 'admin.b2b.attributes.index',
        'sort' => 2,
        'icon' => '',
    ], [
        'key' => 'b2b.company-catalogs',
        'name' => 'b2b::app.admin.layouts.company-catalogs',
        'route' => 'admin.b2b.company_catalogs.index',
        'sort' => 3,
        'icon' => '',
    ], [
        'key' => 'b2b.company-credit',
        'name' => 'b2b::app.admin.layouts.company-credit',
        'route' => 'admin.b2b.company_credits.index',
        'sort' => 4,
        'icon' => '',
    ],

    /**
     * Sales documents.
     */
    [
        'key' => 'b2b.quotes',
        'name' => 'b2b::app.admin.layouts.quotes',
        'route' => 'admin.b2b.quotes.index',
        'sort' => 5,
        'icon' => '',
    ], [
        'key' => 'b2b.purchase-orders',
        'name' => 'b2b::app.admin.layouts.purchase-orders',
        'route' => 'admin.b2b.purchase_orders.index',
        'sort' => 6,
        'icon' => '',
    ],
];
