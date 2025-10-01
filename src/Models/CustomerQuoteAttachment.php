<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteAttachment as CustomerQuoteAttachmentContract;

class CustomerQuoteAttachment extends Model implements CustomerQuoteAttachmentContract
{
    protected $table = 'customer_quote_attachments';

    protected $fillable = [
        'customer_quote_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'customer_quote_id');
    }
}
