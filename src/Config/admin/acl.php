<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B2B Suite ACL
    |--------------------------------------------------------------------------
    |
    | Top-level B2B Suite permission. All B2B permissions live underneath it.
    |
    */
    [
        'key' => 'b2b',
        'name' => 'b2b_suite::app.admin.acl.b2b-suite',
        'route' => 'admin.b2b.companies.index',
        'sort' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Companies ACL
    |--------------------------------------------------------------------------
    */
    [
        'key' => 'b2b.companies',
        'name' => 'b2b_suite::app.admin.acl.companies',
        'route' => 'admin.b2b.companies.index',
        'sort' => 1,
    ], [
        'key' => 'b2b.companies.create',
        'name' => 'b2b_suite::app.admin.acl.create',
        'route' => 'admin.b2b.companies.create',
        'sort' => 1,
    ], [
        'key' => 'b2b.companies.edit',
        'name' => 'b2b_suite::app.admin.acl.edit',
        'route' => 'admin.b2b.companies.edit',
        'sort' => 2,
    ], [
        'key' => 'b2b.companies.delete',
        'name' => 'b2b_suite::app.admin.acl.delete',
        'route' => 'admin.b2b.companies.delete',
        'sort' => 3,
    ], [
        'key' => 'b2b.companies.assign_product',
        'name' => 'b2b_suite::app.admin.acl.assign-product',
        'route' => 'admin.b2b.companies.assign_product',
        'sort' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotations ACL
    |--------------------------------------------------------------------------
    */
    [
        'key' => 'b2b.quotes',
        'name' => 'b2b_suite::app.admin.acl.quotes',
        'route' => 'admin.b2b.quotes.index',
        'sort' => 2,
    ], [
        'key' => 'b2b.quotes.view',
        'name' => 'b2b_suite::app.admin.acl.view',
        'route' => 'admin.b2b.quotes.view',
        'sort' => 1,
    ], [
        'key' => 'b2b.quotes.create',
        'name' => 'b2b_suite::app.admin.acl.create',
        'route' => 'admin.b2b.quotes.create',
        'sort' => 2,
    ], [
        'key' => 'b2b.quotes.edit',
        'name' => 'b2b_suite::app.admin.acl.edit',
        'route' => 'admin.b2b.quotes.submit_quote',
        'sort' => 3,
    ], [
        'key' => 'b2b.quotes.delete',
        'name' => 'b2b_suite::app.admin.acl.delete',
        'route' => 'admin.b2b.quotes.mass_delete',
        'sort' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Purchase Orders ACL
    |--------------------------------------------------------------------------
    */
    [
        'key' => 'b2b.purchase-orders',
        'name' => 'b2b_suite::app.admin.acl.purchase-orders',
        'route' => 'admin.b2b.purchase_orders.index',
        'sort' => 3,
    ], [
        'key' => 'b2b.purchase-orders.view',
        'name' => 'b2b_suite::app.admin.acl.view',
        'route' => 'admin.b2b.purchase_orders.view',
        'sort' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Attributes ACL
    |--------------------------------------------------------------------------
    */
    [
        'key' => 'b2b.attributes',
        'name' => 'b2b_suite::app.admin.acl.attributes',
        'route' => 'admin.b2b.attributes.index',
        'sort' => 4,
    ], [
        'key' => 'b2b.attributes.create',
        'name' => 'b2b_suite::app.admin.acl.create',
        'route' => 'admin.b2b.attributes.create',
        'sort' => 1,
    ], [
        'key' => 'b2b.attributes.edit',
        'name' => 'b2b_suite::app.admin.acl.edit',
        'route' => 'admin.b2b.attributes.edit',
        'sort' => 2,
    ], [
        'key' => 'b2b.attributes.delete',
        'name' => 'b2b_suite::app.admin.acl.delete',
        'route' => 'admin.b2b.attributes.delete',
        'sort' => 3,
    ],
];
