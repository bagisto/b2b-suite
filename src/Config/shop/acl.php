<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Shop Account ACLs
    |--------------------------------------------------------------------------
    |
    | Including standard Bagisto customer account menu items so they can
    | also be permission-controlled under the B2B Suite company roles.
    |
    */ [
        'key'   => 'profile',
        'name'  => 'b2b_suite::app.shop.acl.profile',
        'route' => 'shop.customers.account.profile.index',
        'sort'  => 1,
    ], [
        'key'   => 'profile.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.profile.index',
        'sort'  => 1,
    ], [
        'key'   => 'profile.edit',
        'name'  => 'b2b_suite::app.shop.acl.edit',
        'route' => 'shop.customers.account.profile.edit',
        'sort'  => 2,
    ], [
        'key'   => 'profile.update',
        'name'  => 'b2b_suite::app.shop.acl.update',
        'route' => 'shop.customers.account.profile.update',
        'sort'  => 3,
    ], [
        'key'   => 'profile.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.profile.destroy',
        'sort'  => 4,
    ],
    [
        'key'   => 'address',
        'name'  => 'b2b_suite::app.shop.acl.address',
        'route' => 'shop.customers.account.addresses.index',
        'sort'  => 2,
    ], [
        'key'   => 'address.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.addresses.index',
        'sort'  => 1,
    ], [
        'key'   => 'address.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.addresses.create',
        'sort'  => 2,
    ], [
        'key'   => 'address.edit',
        'name'  => 'b2b_suite::app.shop.acl.edit',
        'route' => 'shop.customers.account.addresses.edit',
        'sort'  => 3,
    ], [
        'key'   => 'address.update',
        'name'  => 'b2b_suite::app.shop.acl.update',
        'route' => 'shop.customers.account.addresses.update',
        'sort'  => 4,
    ], [
        'key'   => 'address.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.addresses.delete',
        'sort'  => 5,
    ],
    [
        'key'   => 'orders',
        'name'  => 'b2b_suite::app.shop.acl.orders',
        'route' => 'shop.customers.account.orders.index',
        'sort'  => 3,
    ], [
        'key'   => 'orders.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.orders.view',
        'sort'  => 1,
    ], [
        'key'   => 'orders.reorder',
        'name'  => 'b2b_suite::app.shop.acl.reorder',
        'route' => 'shop.customers.account.orders.reorder',
        'sort'  => 2,
    ], [
        'key'   => 'orders.cancel',
        'name'  => 'b2b_suite::app.shop.acl.cancel',
        'route' => 'shop.customers.account.orders.cancel',
        'sort'  => 3,
    ], [
        'key'   => 'orders.print-invoice',
        'name'  => 'b2b_suite::app.shop.acl.print-invoice',
        'route' => 'shop.customers.account.orders.print-invoice',
        'sort'  => 4,
    ],
    [
        'key'   => 'downloadables',
        'name'  => 'b2b_suite::app.shop.acl.downloadables',
        'route' => 'shop.customers.account.downloadable_products.index',
        'sort'  => 4,
    ], [
        'key'   => 'downloadables.download',
        'name'  => 'b2b_suite::app.shop.acl.download',
        'route' => 'shop.customers.account.downloadable_products.download',
        'sort'  => 1,
    ],
    [
        'key'   => 'reviews',
        'name'  => 'b2b_suite::app.shop.acl.reviews',
        'route' => 'shop.customers.account.reviews.index',
        'sort'  => 5,
    ], [
        'key'   => 'reviews.index',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.reviews.index',
        'sort'  => 1,
    ],
    [
        'key'   => 'wishlist',
        'name'  => 'b2b_suite::app.shop.acl.wishlist',
        'route' => 'shop.customers.account.wishlist.index',
        'sort'  => 6,
    ],
    [
        'key'   => 'wishlist.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.wishlist.index',
        'sort'  => 1,
    ], [
        'key'   => 'gdpr_data_request',
        'name'  => 'b2b_suite::app.shop.acl.gdpr-request',
        'route' => 'shop.customers.account.gdpr.index',
        'sort'  => 7,
    ],
    [
        'key'   => 'gdpr_data_request.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.gdpr.index',
        'sort'  => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Requisitions ACLs
    |--------------------------------------------------------------------------
    |
    | All ACLs related to requisitions will be placed here.
    |
    */
    [
        'key'   => 'requisitions',
        'name'  => 'b2b_suite::app.shop.acl.requisitions',
        'route' => 'shop.customers.account.requisitions.index',
        'sort'  => 8,
    ], [
        'key'   => 'requisitions.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.requisitions.create',
        'sort'  => 1,
    ], [
        'key'   => 'requisitions.edit',
        'name'  => 'b2b_suite::app.shop.acl.edit',
        'route' => 'shop.customers.account.requisitions.edit',
        'sort'  => 2,
    ], [
        'key'   => 'requisitions.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.requisitions.delete',
        'sort'  => 3,
    ], [
        'key'   => 'requisitions.list',
        'name'  => 'b2b_suite::app.shop.acl.list',
        'route' => 'shop.customers.account.requisitions.list',
        'sort'  => 4,
    ], [
        'key'   => 'requisitions.get-product',
        'name'  => 'b2b_suite::app.shop.acl.get-product',
        'route' => 'shop.customers.account.requisitions.get_product',
        'sort'  => 5,
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
        'key'   => 'quotes',
        'name'  => 'b2b_suite::app.shop.acl.quotes',
        'route' => 'shop.customers.account.quotes.index',
        'sort'  => 9,
    ], [
        'key'   => 'quotes.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.quotes.view',
        'sort'  => 1,
    ], [
        'key'   => 'quotes.delete',
        'name'  => 'b2b_suite::app.shop.acl.delete',
        'route' => 'shop.customers.account.quotes.delete_quote',
        'sort'  => 2,
    ], [
        'key'   => 'quotes.messages',
        'name'  => 'b2b_suite::app.shop.acl.messages',
        'route' => 'shop.customers.account.quotes.messages',
        'sort'  => 3,
    ], [
        'key'   => 'quotes.get-product',
        'name'  => 'b2b_suite::app.shop.acl.get-product',
        'route' => 'shop.customers.account.quotes.get_product',
        'sort'  => 4,
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
        'key'   => 'purchase-orders',
        'name'  => 'b2b_suite::app.shop.acl.purchase-orders',
        'route' => 'shop.customers.account.purchase_orders.index',
        'sort'  => 10,
    ], [
        'key'   => 'purchase-orders.view',
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
        'key'   => 'quick-orders',
        'name'  => 'b2b_suite::app.shop.acl.quick-orders',
        'route' => 'shop.customers.account.quick_orders.index',
        'sort'  => 11,
    ], [
        'key'   => 'quick-orders.view',
        'name'  => 'b2b_suite::app.shop.acl.view',
        'route' => 'shop.customers.account.quick_orders.index',
        'sort'  => 1,
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
        'key'   => 'users',
        'name'  => 'b2b_suite::app.shop.acl.users',
        'route' => 'shop.customers.account.users.index',
        'sort'  => 22,
    ], [
        'key'   => 'users.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.users.create',
        'sort'  => 23,
    ], [
        'key'   => 'users.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'shop.customers.account.users.edit',
        'sort'  => 24,
    ], [
        'key'   => 'users.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'shop.customers.account.users.delete',
        'sort'  => 25,
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
        'key'   => 'roles',
        'name'  => 'b2b_suite::app.shop.acl.roles',
        'route' => 'shop.customers.account.roles.index',
        'sort'  => 26,
    ], [
        'key'   => 'roles.create',
        'name'  => 'b2b_suite::app.shop.acl.create',
        'route' => 'shop.customers.account.roles.create',
        'sort'  => 27,
    ], [
        'key'   => 'roles.edit',
        'name'  => 'admin::app.acl.edit',
        'route' => 'shop.customers.account.roles.edit',
        'sort'  => 28,
    ], [
        'key'   => 'roles.delete',
        'name'  => 'admin::app.acl.delete',
        'route' => 'shop.customers.account.roles.delete',
        'sort'  => 29,
    ],
];
