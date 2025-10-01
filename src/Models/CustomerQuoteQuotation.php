<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteQuotation as CustomerQuoteQuotationContract;

class CustomerQuoteQuotation extends Model implements CustomerQuoteQuotationContract
{
    protected $table = 'customer_quote_quotations';

    protected $fillable = [
        'message_id',
        'quote_id',
        'quote_item_id',
        'sku',
        'name',
        'qty',
        'price',
        'base_price',
        'total',
        'base_total',
        'is_accepted',
        'accepted_by',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CustomerQuoteMessage::class, 'message_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'quote_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(CustomerQuoteItem::class, 'quote_item_id');
    }
}
