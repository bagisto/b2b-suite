<?php

namespace Webkul\B2BSuite\Events;

use Webkul\B2BSuite\Models\Company;

class CompanyCreated
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Company $company
    ) {}
}
