<?php

use Illuminate\Support\Facades\Route;
use Webkul\B2BSuite\Http\Controllers\Admin\CartController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyAttributeController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyCatalogController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyCreditController;
use Webkul\B2BSuite\Http\Controllers\Admin\PurchaseOrderController;
use Webkul\B2BSuite\Http\Controllers\Admin\QuoteController;

/**
 * ----------------------------------------------------
 * All the company attributes routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(CompanyAttributeController::class)->prefix('attributes')->group(function () {
    Route::get('', 'index')->name('admin.b2b.attributes.index');

    Route::get('create', 'create')->name('admin.b2b.attributes.create');

    Route::post('', 'store')->name('admin.b2b.attributes.store');

    Route::get('edit/{id}', 'edit')->name('admin.b2b.attributes.edit');

    Route::put('edit/{id}', 'update')->name('admin.b2b.attributes.update');

    Route::delete('delete/{id}', 'destroy')->name('admin.b2b.attributes.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.b2b.attributes.mass_delete');

    Route::get('mapping', 'editMapping')->name('admin.b2b.attributes.edit_mapping');

    Route::post('mapping', 'updateMapping')->name('admin.b2b.attributes.update_mapping');
});

/**
 * ----------------------------------------------------
 * All the companies routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(CompanyController::class)->prefix('companies')->group(function () {
    Route::get('', 'index')->name('admin.b2b.companies.index');

    Route::get('fetch', 'get')->name('admin.b2b.companies.get');

    Route::get('search', 'search')->name('admin.b2b.companies.search');

    Route::get('create', 'create')->name('admin.b2b.companies.create');

    Route::post('create', 'store')->name('admin.b2b.companies.store');

    Route::get('edit/{id}', 'edit')->name('admin.b2b.companies.edit');

    Route::put('edit/{id}', 'update')->name('admin.b2b.companies.update');

    Route::post('{id}/status', 'updateStatus')->name('admin.b2b.companies.update_status');

    Route::delete('edit/{id}', 'destroy')->name('admin.b2b.companies.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.b2b.companies.mass_delete');

    Route::post('mass-update-status', 'massUpdateStatus')->name('admin.b2b.companies.mass_update_status');
});

/**
 * Company credit (Pay By Credit) management.
 */
Route::controller(CompanyCreditController::class)->prefix('companies')->group(function () {
    Route::get('credits', 'index')->name('admin.b2b.companies.credits.index');

    Route::get('{id}/credit', 'show')->name('admin.b2b.companies.credit');

    Route::post('{id}/credit/limit', 'updateLimit')->name('admin.b2b.companies.credit.limit');

    Route::post('{id}/credit/reimburse', 'reimburse')->name('admin.b2b.companies.credit.reimburse');
});

/**
 * ----------------------------------------------------
 * All the company quotes routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(QuoteController::class)->prefix('quotes')->group(function () {
    Route::get('', 'index')->name('admin.b2b.quotes.index');

    Route::get('create/{cartId}', 'create')->name('admin.b2b.quotes.create');

    Route::post('create/{cartId}', 'store')->name('admin.b2b.quotes.store');

    Route::get('{id}', 'view')->name('admin.b2b.quotes.view');

    Route::get('{id}/messages', 'getMessages')->name('admin.b2b.quotes.messages');

    Route::post('{id}/send-message', 'sendMessage')->name('admin.b2b.quotes.send_message');

    Route::post('{id}/reject-quote', 'rejectQuote')->name('admin.b2b.quotes.reject_quote');

    Route::post('{id}/accept-quote', 'acceptQuote')->name('admin.b2b.quotes.accept_quote');

    Route::post('{id}/submit', 'submitQuote')->name('admin.b2b.quotes.submit_quote');

    Route::post('mass-delete', 'massDestroy')->name('admin.b2b.quotes.mass_delete');

    Route::post('mass-update', 'massUpdate')->name('admin.b2b.quotes.mass_update');
});

/**
 * ----------------------------------------------------
 * All the company purchase orders routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->group(function () {
    Route::get('', 'index')->name('admin.b2b.purchase_orders.index');

    Route::get('{id}', 'view')->name('admin.b2b.purchase_orders.view');
});

/**
 * ----------------------------------------------------
 * All the company catalog routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(CompanyCatalogController::class)->prefix('company-catalogs')->group(function () {
    Route::get('', 'index')->name('admin.b2b.company_catalogs.index');

    Route::get('companies', 'companies')->name('admin.b2b.company_catalogs.companies');

    Route::get('products', 'products')->name('admin.b2b.company_catalogs.products');

    Route::get('product-children/{id}', 'productChildren')->name('admin.b2b.company_catalogs.product_children');

    Route::post('category-preview', 'categoryPreview')->name('admin.b2b.company_catalogs.category_preview');

    Route::get('create', 'create')->name('admin.b2b.company_catalogs.create');

    Route::post('create', 'store')->name('admin.b2b.company_catalogs.store');

    Route::get('edit/{id}', 'edit')->name('admin.b2b.company_catalogs.edit');

    Route::put('edit/{id}', 'update')->name('admin.b2b.company_catalogs.update');

    Route::put('settings/{id}', 'updateSettings')->name('admin.b2b.company_catalogs.settings');

    Route::delete('delete/{id}', 'destroy')->name('admin.b2b.company_catalogs.delete');
});

Route::controller(CartController::class)->prefix('cart')->group(function () {
    Route::get('{id}', 'index')->name('admin.b2b.cart.index');

    Route::post('create', 'store')->name('admin.b2b.cart.store');

    Route::post('{id}/items', 'storeItem')->name('admin.b2b.cart.items.store');

    Route::put('{id}/items', 'updateItem')->name('admin.b2b.cart.items.update');

    Route::delete('{id}/items', 'destroyItem')->name('admin.b2b.cart.items.destroy');
});
