<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CustomerQuoteAttachment as CustomerQuoteAttachmentContract;

class CustomerQuoteAttachment extends Model implements CustomerQuoteAttachmentContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_customer_quote_attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_quote_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * The quote this attachment belongs to.
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(CustomerQuote::class, 'customer_quote_id');
    }
}
