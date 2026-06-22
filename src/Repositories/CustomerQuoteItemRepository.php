<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CustomerQuoteItem;
use Webkul\Core\Eloquent\Repository;

class CustomerQuoteItemRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model()
    {
        return CustomerQuoteItem::class;
    }

    /**
     * Create a new quote item record.
     */
    public function create(array $data): CustomerQuoteItem
    {
        return $this->model->create($data);
    }

    /**
     * Check if a product has any accepted negotiated quote for a customer.
     *
     * @param  int  $productId
     * @param  int  $customerId
     */
    public function hasAcceptedNegotiation($productId, $customerId): bool
    {
        return $this->model
            ->join('b2b_customer_quotes', 'b2b_customer_quote_items.customer_quote_id', '=', 'b2b_customer_quotes.id')
            ->where('b2b_customer_quote_items.product_id', $productId)
            ->where('b2b_customer_quotes.customer_id', $customerId)
            ->where('b2b_customer_quotes.status', 'accepted')
            ->exists();
    }
}
