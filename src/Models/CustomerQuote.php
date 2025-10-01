<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CustomerQuote as CustomerQuoteContract;
use Webkul\Checkout\Models\Cart;

class CustomerQuote extends Model implements CustomerQuoteContract
{
    protected $table = 'customer_quotes';

    /**
     * Quotation state.
     */
    public const STATE_QUOTATION = 'quotation';

    /**
     * Purchase Order state.
     */
    public const STATE_PURCHASE_ORDER = 'purchase_order';

    /**
     * Draft status.
     */
    public const STATUS_DRAFT = 'draft';

    /**
     * Open/Pending status.
     */
    public const STATUS_OPEN = 'open';

    /**
     * Negotiation/Processing status.
     */
    public const STATUS_NEGOTIATION = 'negotiation';

    /**
     * Accepted/Confirmed status.
     */
    public const STATUS_ACCEPTED = 'accepted';

    /**
     * Ordered status.
     */
    public const STATUS_ORDERED = 'ordered';

    /**
     * Expired status.
     */
    public const STATUS_EXPIRED = 'expired';

    /**
     * Rejected status.
     */
    public const STATUS_REJECTED = 'rejected';

    /**
     * Closed/Completed status.
     */
    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'quotation_number',
        'po_number',
        'shipping_number',
        'name',
        'description',
        'company_id',
        'customer_id',
        'agent_id',
        'total',
        'base_total',
        'negotiated_total',
        'base_negotiated_total',
        'order_date',
        'expected_arrival_date',
        'expiration_date',
        'state',
        'status',
        'soft_deleted',
        'order_id',
    ];

    /**
     * Status label.
     *
     * @var array
     */
    protected static $statusLabel = [
        self::STATUS_DRAFT       => 'Draft',
        self::STATUS_OPEN        => 'Open',
        self::STATUS_NEGOTIATION => 'Negotiation',
        self::STATUS_ACCEPTED    => 'Accepted',
        self::STATUS_ORDERED     => 'Ordered',
        self::STATUS_EXPIRED     => 'Expired',
        self::STATUS_REJECTED    => 'Rejected',
        self::STATUS_COMPLETED   => 'Completed',
    ];

    /**
     * Returns the status label array.
     */
    public static function statusLabel()
    {
        return self::$statusLabel;
    }

    /**
     * Returns the status label from status code (for Eloquent attribute access).
     */
    public function getStatusLabelAttribute()
    {
        return self::$statusLabel;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'company_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(\Webkul\User\Models\Admin::class, 'agent_id');
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerQuoteItem::class, 'customer_quote_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(CustomerQuoteQuotation::class, 'quote_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CustomerQuoteMessage::class, 'quote_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CustomerQuoteAttachment::class, 'customer_quote_id');
    }
}
