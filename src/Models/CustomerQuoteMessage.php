<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CustomerQuoteMessage as CustomerQuoteMessageContract;

class CustomerQuoteMessage extends Model implements CustomerQuoteMessageContract
{
    protected $table = 'customer_quote_messages';

    protected $fillable = [
        'quote_id',
        'user_id',
        'user_type',
        'message',
        'status',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'quote_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(CustomerQuoteQuotation::class, 'message_id');
    }
}
