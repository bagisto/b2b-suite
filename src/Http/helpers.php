<?php

use Webkul\B2BSuite\Acl;
use Webkul\B2BSuite\CustomerBouncer;
use Webkul\B2BSuite\Facades\B2BSuite;

/**
 * -------------------------
 * B2B Suite helper.
 * -------------------------
 */
if (! function_exists('b2b')) {
    /**
     * B2BSuite helper.
     *
     * @return Webkul\B2BSuite\B2BSuite
     */
    function b2b()
    {
        return B2BSuite::getFacadeRoot();
    }
}

/**
 * -------------------------
 * B2B Suite Acl helper.
 * -------------------------
 */
if (! function_exists('b2b_acl')) {
    /**
     * B2B Suite ACL helper.
     */
    function b2b_acl(): Acl
    {
        return app(Acl::class);
    }
}

/**
 * -------------------------
 * Customer Bouncer helper.
 * -------------------------
 */
if (! function_exists('customer_bouncer')) {
    /**
     * Customer Bouncer helper.
     */
    function customer_bouncer(): CustomerBouncer
    {
        return app()->make(CustomerBouncer::class);
    }
}
