<?php

use Webkul\B2BSuite\Acl;
use Webkul\B2BSuite\Facades\B2BSuite;
use Webkul\B2BSuite\CustomerBouncer;

/**
 * -------------------------
 * B2B Suite Acl helper.
 * -------------------------
 */
if (! function_exists('b2b_suite_acl')) {
    function b2b_suite_acl(): Acl
    {
        return app(Acl::class);
    }
}
/**
 * -------------------------
 * B2B Suite helper.
 * -------------------------
 */
if (! function_exists('b2b_suite')) {
    /**
     * B2BSuite helper.
     *
     * @return \Webkul\B2BSuite\B2BSuite
     */
    function b2b_suite()
    {
        return B2BSuite::getFacadeRoot();
    }
}

/**
 * -------------------------
 * Customer Bouncer helper.
 * -------------------------
 */
if (! function_exists('customer_bouncer')) {
    function customer_bouncer(): CustomerBouncer
    {
        return app()->make(CustomerBouncer::class);
    }
}
