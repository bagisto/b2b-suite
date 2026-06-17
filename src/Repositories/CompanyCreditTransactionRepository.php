<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CompanyCreditTransaction;
use Webkul\Core\Eloquent\Repository;

class CompanyCreditTransactionRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CompanyCreditTransaction::class;
    }
}
