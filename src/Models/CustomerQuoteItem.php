<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteItem as CustomerQuoteItemContract;
use Webkul\Product\Models\Product;

class CustomerQuoteItem extends Model implements CustomerQuoteItemContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_quote_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        'discount_type',
        'discount_value',
        'note',
        'status',
        'additional',
    ];

    /**
     * The quote this item belongs to.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'customer_quote_id');
    }

    /**
     * The product this item references.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
