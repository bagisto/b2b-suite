<?php

return [
    /*
    |--------------------------------------------------------------------------
    | B2B Suite Attributes ACL
    |--------------------------------------------------------------------------
    |
    | All ACLs related to attributes will be placed here.
    |
    */
    [
        'key'   => 'customers.attributes',
        'name'  => 'b2b_suite::app.admin.acl.attributes',
        'route' => 'admin.customers.attributes.index',
        'sort'  => 1,
    ], [
        'key'   => 'customers.attributes.create',
        'name'  => 'b2b_suite::app.admin.acl.create',
        'route' => 'admin.customers.attributes.create',
        'sort'  => 1,
    ], [
        'key'   => 'customers.attributes.edit',
        'name'  => 'b2b_suite::app.admin.acl.edit',
        'route' => 'admin.customers.attributes.edit',
        'sort'  => 2,
    ], [
        'key'   => 'customers.attributes.delete',
        'name'  => 'b2b_suite::app.admin.acl.delete',
        'route' => 'admin.customers.attributes.delete',
        'sort'  => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | B2B Suite Company ACL
    |--------------------------------------------------------------------------
    |
    | All ACLs related to companies will be placed here.
    |
    */
    [
        'key'   => 'customers.companies',
        'name'  => 'b2b_suite::app.admin.acl.companies',
        'route' => 'admin.customers.companies.index',
        'sort'  => 1,
    ], [
        'key'   => 'customers.companies.create',
        'name'  => 'b2b_suite::app.admin.acl.create',
        'route' => 'admin.customers.companies.create',
        'sort'  => 1,
    ], [
        'key'   => 'customers.companies.edit',
        'name'  => 'b2b_suite::app.admin.acl.edit',
        'route' => 'admin.customers.companies.edit',
        'sort'  => 2,
    ], [
        'key'   => 'customers.companies.delete',
        'name'  => 'b2b_suite::app.admin.acl.delete',
        'route' => 'admin.customers.companies.delete',
        'sort'  => 3,
    ], [
        'key'   => 'customers.companies.assign_product',
        'name'  => 'b2b_suite::app.admin.acl.assign-product',
        'route' => 'admin.customers.companies.assign_product',
        'sort'  => 4,
    ],
];
