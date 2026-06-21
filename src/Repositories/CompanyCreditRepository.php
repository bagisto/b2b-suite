<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CompanyCredit;
use Webkul\Core\Eloquent\Repository;

class CompanyCreditRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model()
    {
        return CompanyCredit::class;
    }

    /**
     * Find a credit row by id with a pessimistic lock for safe concurrent updates.
     */
    public function findForUpdate($id): ?CompanyCredit
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }
}
