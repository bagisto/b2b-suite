<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteItem as CustomerQuoteItemContract;

class CustomerQuoteItem extends Model implements CustomerQuoteItemContract
{
    protected $table = 'customer_quote_items';

    protected $fillable = [
        'customer_quote_id',
        'product_id',
        'type',
        'sku',
        'name',
        'description',
        'qty',
        'price',
        'base_price',
        'total',
        'base_total',
        'negotiated_qty',
        'negotiated_price',
        'base_negotiated_price',
        'negotiated_total',
        'base_negotiated_total',
        'note',
        'status',
        'additional',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'customer_quote_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\Webkul\Product\Models\Product::class, 'product_id');
    }
}
