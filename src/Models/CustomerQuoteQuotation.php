<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteQuotation as CustomerQuoteQuotationContract;

class CustomerQuoteQuotation extends Model implements CustomerQuoteQuotationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_quote_quotations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'message_id',
        'quote_id',
        'quote_item_id',
        'sku',
        'name',
        'qty',
        'discount_type',
        'discount_value',
        'price',
        'base_price',
        'total',
        'base_total',
        'is_accepted',
        'accepted_by',
    ];

    /**
     * The message this quotation was sent in.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(CustomerQuoteMessage::class, 'message_id');
    }

    /**
     * The quote this quotation belongs to.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'quote_id');
    }

    /**
     * The quote item this quotation negotiates.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(CustomerQuoteItem::class, 'quote_item_id');
    }
}
