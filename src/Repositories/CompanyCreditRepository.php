<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CompanyCredit;
use Webkul\Core\Eloquent\Repository;

class CompanyCreditRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CompanyCredit::class;
    }
}
