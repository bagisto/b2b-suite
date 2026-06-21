<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CompanyAttributeOption;
use Webkul\Core\Eloquent\Repository;

class CompanyAttributeOptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return CompanyAttributeOption::class;
    }
}
