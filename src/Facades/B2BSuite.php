<?php

namespace Webkul\B2BSuite\Facades;

use Illuminate\Support\Facades\Facade;
use Webkul\B2BSuite\B2BSuite as BaseB2BSuite;

class B2BSuite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BaseB2BSuite::class;
    }
}
