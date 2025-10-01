<?php

namespace Webkul\B2BSuite\Repositories;

use Webkul\B2BSuite\Contracts\CustomerQuoteMessage;
use Webkul\Core\Eloquent\Repository;

class CustomerQuoteMessageRepository extends Repository
{
    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CustomerQuoteMessage::class;
    }

    /**
     * Get the last quotation message for a specific quote and user type.
     *
     * @param  int  $quoteId
     * @param  string  $userType
     * @return \Webkul\B2BSuite\Models\CustomerQuoteMessage|null
     */
    public function getLastQuotationMessage($quoteId, $userType)
    {
        return $this->model->where('quote_id', $quoteId)
            ->where('user_type', $userType)
            ->whereHas('quotations')
            ->with('quotations')
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
