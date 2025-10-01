<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Companies Roles ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to sales will be placed here.
    |
    */
    [
        'key'   => 'account',
        'name'  => 'b2b_suite::app.shop.acl.requisitions',
        'route' => 'shop.customers.account.requisitions.index',
        'sort'  => 1,
    ], [
        'key'   => 'account.requisitions',
        'name'  => 'b2b_suite::app.shop.acl.requisitions',
        'route' => 'shop.customers.account.requisitions.index',
        'sort'  => 1,
    ], [
        'key'   => 'account.requisitions.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.requisitions.create',
        'sort'  => 2,
    ], [
        'key'   => 'account.requisitions.edit',
        'name'  => 'b2b_suite::app.shop.acl.edit',
        'route' => 'shop.customers.account.requisitions.edit',
        'sort'  => 3,
    ], [
        'key'   => 'account.requisitions.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.requisitions.delete',
        'sort'  => 4,
    ], [
        'key'   => 'account.requisitions.list',
        'name'  => 'b2b_suite::app.shop.acl.list',
        'route' => 'shop.customers.account.requisitions.list',
        'sort'  => 5,
    ], [
        'key'   => 'account.requisitions.get-product',
        'name'  => 'b2b_suite::app.shop.acl.get-product',
        'route' => 'shop.customers.account.requisitions.get_product',
        'sort'  => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quotes ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to quotes will be placed here.
    |
    */
    [
        'key'   => 'account.quotes',
        'name'  => 'b2b_suite::app.shop.acl.quotes',
        'route' => 'shop.customers.account.quotes.index',
        'sort'  => 2,
    // ], [
    //     'key'   => 'account.quotes.create',
    //     'name'  => 'b2b_suite::app.shop.acl.create',
    //     'route' => 'shop.customers.account.quotes.create',
    //     'sort'  => 1,
    ], [
        'key'   => 'account.quotes.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.quotes.view',
        'sort'  => 2,
    ], [
        'key'   => 'account.quotes.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.quotes.delete_quote',
        'sort'  => 3,
    ], [
        'key'   => 'account.quotes.messages',
        'name'  => 'b2b_suite::app.shop.acl.messages',
        'route' => 'shop.customers.account.quotes.messages',
        'sort'  => 4,
    ], [
        'key'   => 'account.quotes.get-product',
        'name'  => 'b2b_suite::app.shop.acl.get-product',
        'route' => 'shop.customers.account.quotes.get_product',
        'sort'  => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Purchase Orders ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to purchase orders will be placed here.
    |
    */
    [
        'key'   => 'account.purchase-orders',
        'name'  => 'b2b_suite::app.shop.acl.purchase-orders',
        'route' => 'shop.customers.account.purchase_orders.index',
        'sort'  => 3,
    ], [
        'key'   => 'account.purchase-orders.view',
        'name'  => 'admin::app.acl.view',
        'route' => 'shop.customers.account.purchase_orders.view',
        'sort'  => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Quick Orders ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to quick orders will be placed here.
    |
    */
    [
        'key'   => 'account.quick-orders',
        'name'  => 'b2b_suite::app.shop.acl.quick-orders',
        'route' => 'shop.customers.account.quick_orders.index',
        'sort'  => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Users ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to company users will be placed here.
    |
    */
    [
        'key'   => 'account.users',
        'name'  => 'b2b_suite::app.shop.acl.users',
        'route' => 'shop.customers.account.users.index',
        'sort'  => 5,
    ], [
        'key'   => 'account.users.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.users.create',
        'sort'  => 1,
    ], [
        'key'   => 'account.users.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'shop.customers.account.users.edit',
        'sort'  => 2,
    ], [
        'key'   => 'account.users.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'shop.customers.account.users.delete',
        'sort'  => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Company Roles ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to company roles will be placed here.
    |
    */
    [
        'key'   => 'account.roles',
        'name'  => 'b2b_suite::app.shop.acl.roles',
        'route' => 'shop.customers.account.roles.index',
        'sort'  => 6,
    ], [
        'key'   => 'account.roles.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.roles.create',
        'sort'  => 1,
    ], [
        'key'   => 'account.roles.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'shop.customers.account.roles.edit',
        'sort'  => 2,
    ], [
        'key'   => 'account.roles.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'shop.customers.account.roles.delete',
        'sort'  => 3,
    ],
];
