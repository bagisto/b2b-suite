<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CompanyCatalog;
use Webkul\Core\Eloquent\Repository;

class CompanyCatalogRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model()
    {
        return CompanyCatalog::class;
    }
}
