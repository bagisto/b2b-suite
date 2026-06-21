<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

/**
 * Storefront breadcrumbs for the B2B / "Company" account section. All hang off the
 * core `account` trail (Home > My Account). Registered from B2BSuiteServiceProvider::boot().
 */

/**
 * Home > My Account > Company Profile
 */
Breadcrumbs::for('company.profile', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.company-profile'), route('shop.customers.account.company_profile.index'));
});

/**
 * Home > My Account > Requisitions
 */
Breadcrumbs::for('requisitions', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.requisitions'), route('shop.customers.account.requisitions.index'));
});

Breadcrumbs::for('requisitions.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('requisitions');
    $trail->push(trans('b2b::app.shop.acl.edit'));
});

/**
 * Home > My Account > Quotations
 */
Breadcrumbs::for('quotes', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.quotes'), route('shop.customers.account.quotes.index'));
});

Breadcrumbs::for('quotes.view', function (BreadcrumbTrail $trail) {
    $trail->parent('quotes');
    $trail->push(trans('b2b::app.shop.acl.view'));
});

/**
 * Home > My Account > Purchase Orders
 */
Breadcrumbs::for('purchase-orders', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.purchase-orders'), route('shop.customers.account.purchase_orders.index'));
});

Breadcrumbs::for('purchase-orders.view', function (BreadcrumbTrail $trail) {
    $trail->parent('purchase-orders');
    $trail->push(trans('b2b::app.shop.acl.view'));
});

/**
 * Home > My Account > Quick Orders
 */
Breadcrumbs::for('quick-orders', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.quick-orders'), route('shop.customers.account.quick_orders.index'));
});

/**
 * Home > My Account > Users
 */
Breadcrumbs::for('users', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.users'), route('shop.customers.account.users.index'));
});

Breadcrumbs::for('users.create', function (BreadcrumbTrail $trail) {
    $trail->parent('users');
    $trail->push(trans('b2b::app.shop.acl.create'));
});

Breadcrumbs::for('users.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('users');
    $trail->push(trans('b2b::app.shop.acl.edit'));
});

/**
 * Home > My Account > Roles
 */
Breadcrumbs::for('roles', function (BreadcrumbTrail $trail) {
    $trail->parent('account');
    $trail->push(trans('b2b::app.shop.acl.roles'), route('shop.customers.account.roles.index'));
});

Breadcrumbs::for('roles.create', function (BreadcrumbTrail $trail) {
    $trail->parent('roles');
    $trail->push(trans('b2b::app.shop.acl.create'));
});

Breadcrumbs::for('roles.edit', function (BreadcrumbTrail $trail) {
    $trail->parent('roles');
    $trail->push(trans('b2b::app.shop.acl.edit'));
});
