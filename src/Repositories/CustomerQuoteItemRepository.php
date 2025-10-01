<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CustomerQuoteItem;
use Webkul\Core\Eloquent\Repository;

class CustomerQuoteItemRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CustomerQuoteItem::class;
    }

    public function create(array $data): CustomerQuoteItem
    {
        return $this->model->create($data);
    }
}
