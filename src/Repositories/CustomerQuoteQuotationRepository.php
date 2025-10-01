<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CustomerQuoteQuotation;
use Webkul\Core\Eloquent\Repository;

class CustomerQuoteQuotationRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CustomerQuoteQuotation::class;
    }

    public function create(array $data): CustomerQuoteQuotation
    {
        return $this->model->create($data);
    }
}
