<?php

use Illuminate\Support\Facades\Route;
use Webkul\B2BSuite\Http\Controllers\Admin\CartController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyAttributeController;
use Webkul\B2BSuite\Http\Controllers\Admin\CompanyController;
use Webkul\B2BSuite\Http\Controllers\Admin\PurchaseOrderController;
use Webkul\B2BSuite\Http\Controllers\Admin\QuoteController;

/**
 * ----------------------------------------------------
 * All the company attributes routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(CompanyAttributeController::class)->prefix('attributes')->group(function () {
    Route::get('', 'index')->name('admin.customers.attributes.index');

    Route::get('create', 'create')->name('admin.customers.attributes.create');

    Route::post('', 'store')->name('admin.customers.attributes.store');

    Route::get('edit/{id}', 'edit')->name('admin.customers.attributes.edit');

    Route::put('edit/{id}', 'update')->name('admin.customers.attributes.update');

    Route::delete('delete/{id}', 'destroy')->name('admin.customers.attributes.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.customers.attributes.mass_delete');

    Route::get('mapping', 'editMapping')->name('admin.customers.attributes.edit_mapping');

    Route::post('mapping', 'updateMapping')->name('admin.customers.attributes.update_mapping');
});

/**
 * ----------------------------------------------------
 * All the companies routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(CompanyController::class)->prefix('companies')->group(function () {
    Route::get('', 'index')->name('admin.customers.companies.index');

    Route::get('companies', 'get')->name('admin.customers.companies.get');

    Route::get('search', 'search')->name('admin.customers.companies.search');

    Route::get('create', 'create')->name('admin.customers.companies.create');

    Route::post('create', 'store')->name('admin.customers.companies.store');

    Route::get('edit/{id}', 'edit')->name('admin.customers.companies.edit');

    Route::put('edit/{id}', 'update')->name('admin.customers.companies.update');

    Route::delete('edit/{id}', 'destroy')->name('admin.customers.companies.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.customers.companies.mass_delete');
});

/**
 * ----------------------------------------------------
 * All the company quotes routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(QuoteController::class)->prefix('quotes')->group(function () {
    Route::get('', 'index')->name('admin.customers.quotes.index');

    Route::get('create/{cartId}', 'create')->name('admin.customers.quotes.create');

    Route::post('create/{cartId}', 'store')->name('admin.customers.quotes.store');

    Route::get('{id}', 'view')->name('admin.customers.quotes.view');

    Route::get('/quotes/{id}/messages', 'getMessages')->name('admin.customers.quotes.messages');

    Route::post('{id}/send-message', 'sendMessage')->name('admin.customers.quotes.send_message');

    Route::post('{id}/reject-quote', 'rejectQuote')->name('admin.customers.quotes.reject_quote');

    Route::post('{id}/submit', 'submitQuote')->name('admin.customers.quotes.submit_quote');

    Route::post('{id}/accept-quote', 'acceptQuote')->name('admin.customers.quotes.accept_quote');

    // Route::get('send-by-mail/{id}', 'sendByMail')->name('admin.customers.quotes.send_by_mail');

    // Route::get('print-order/{id}', 'printOrder')->name('admin.customers.quotes.print_order');

    // Route::post('{id}/delete', 'deleteQuote')->name('admin.customers.quotes.delete_quote');

    // Route::get('edit/{id}', 'edit')->name('admin.customers.quotes.edit');

    // Route::put('edit/{id}', 'update')->name('admin.customers.quotes.update');

    // Route::delete('delete/{id}', 'destroy')->name('admin.customers.quotes.delete');

    Route::post('mass-delete', 'massDestroy')->name('admin.customers.quotes.mass_delete');

    Route::post('mass-update', 'massUpdate')->name('admin.customers.quotes.mass_update');
});

/**
 * ----------------------------------------------------
 * All the company purchase orders routes will be defined here
 * ----------------------------------------------------
 */
Route::controller(PurchaseOrderController::class)->prefix('purchase-orders')->group(function () {
    Route::get('', 'index')->name('admin.customers.purchase_orders.index');

    Route::get('{id}', 'view')->name('admin.customers.purchase_orders.view');
});

Route::controller(CartController::class)->prefix('cart')->group(function () {
    Route::get('{id}', 'index')->name('admin.customers.cart.index');

    Route::post('create', 'store')->name('admin.customers.cart.store');

    Route::post('{id}/items', 'storeItem')->name('admin.customers.cart.items.store');

    Route::put('{id}/items', 'updateItem')->name('admin.customers.cart.items.update');

    Route::delete('{id}/items', 'destroyItem')->name('admin.customers.cart.items.destroy');
});
