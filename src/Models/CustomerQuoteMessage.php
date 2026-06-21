<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CustomerQuoteMessage as CustomerQuoteMessageContract;

class CustomerQuoteMessage extends Model implements CustomerQuoteMessageContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_quote_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_id',
        'user_id',
        'user_type',
        'message',
        'status',
    ];

    /**
     * The quote this message belongs to.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'quote_id');
    }

    /**
     * The quotations sent within this message.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(CustomerQuoteQuotation::class, 'message_id');
    }
}
