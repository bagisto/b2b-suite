<?php

return [
    [
        'key'   => 'b2b',
        'name'  => 'b2b_suite::app.admin.layouts.b2b-suite',
        'route' => 'admin.b2b.companies.index',
        'sort'  => 5,
        'icon'  => 'icon-store',
    ], [
        'key'   => 'b2b.companies',
        'name'  => 'b2b_suite::app.admin.layouts.companies',
        'route' => 'admin.b2b.companies.index',
        'sort'  => 1,
        'icon'  => '',
    ], [
        'key'   => 'b2b.quotes',
        'name'  => 'b2b_suite::app.admin.layouts.quotes',
        'route' => 'admin.b2b.quotes.index',
        'sort'  => 2,
        'icon'  => '',
    ], [
        'key'   => 'b2b.purchase-orders',
        'name'  => 'b2b_suite::app.admin.layouts.purchase-orders',
        'route' => 'admin.b2b.purchase_orders.index',
        'sort'  => 3,
        'icon'  => '',
    ], [
        'key'   => 'b2b.attributes',
        'name'  => 'b2b_suite::app.admin.layouts.attributes',
        'route' => 'admin.b2b.attributes.index',
        'sort'  => 4,
        'icon'  => '',
    ],
];
