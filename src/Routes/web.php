<?php

use Illuminate\Support\Facades\Route;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;

Route::group(['middleware' => ['admin', NoCacheMiddleware::class], 'prefix' => config('app.admin_url').'/b2b'], function () {

    /**
     * Admin routes.
     */
    require 'admin-routes.php';
});

/**
 * Shop routes.
 */
require 'shop-routes.php';
