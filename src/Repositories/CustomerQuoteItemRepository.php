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

    /**
     * Check if a product has any accepted negotiated quote for a customer.
     *
     * @param  int  $productId
     * @param  int  $customerId
     * @return bool
     */
    public function hasAcceptedNegotiation($productId, $customerId): bool
    {
        return $this->model
            ->join('customer_quotes', 'customer_quote_items.customer_quote_id', '=', 'customer_quotes.id')
            ->where('customer_quote_items.product_id', $productId)
            ->where('customer_quotes.customer_id', $customerId)
            ->where('customer_quotes.status', 'accepted')
            ->exists();
    }
}
