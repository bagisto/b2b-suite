<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Webkul\B2BSuite\Contracts\CustomerQuote as CustomerQuoteContract;
use Webkul\Checkout\Models\Cart;
use Webkul\User\Models\Admin;

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
        'customer_name',
        'customer_email',
        'agent_id',
        'total',
        'base_total',
        'negotiated_total',
        'base_negotiated_total',
        'discount_type',
        'discount_value',
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
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_OPEN => 'Open',
        self::STATUS_NEGOTIATION => 'Negotiation',
        self::STATUS_ACCEPTED => 'Accepted',
        self::STATUS_ORDERED => 'Ordered',
        self::STATUS_EXPIRED => 'Expired',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_COMPLETED => 'Completed',
    ];

    /**
     * Admin badge palette: status => admin theme `label-*` class. Single source of truth
     * for the admin datagrids (quote + purchase order) and the admin view pages, so the
     * listing badge and the view-page badge always share a colour for a given status.
     */
    public const STATUS_LABEL_CLASSES = [
        self::STATUS_DRAFT => 'label-info',
        self::STATUS_OPEN => 'label-pending',
        self::STATUS_NEGOTIATION => 'label-closed',
        self::STATUS_ACCEPTED => 'label-processing',
        self::STATUS_ORDERED => 'label-completed',
        self::STATUS_COMPLETED => 'label-active',
        self::STATUS_EXPIRED => 'label-canceled',
        self::STATUS_REJECTED => 'label-canceled',
    ];

    /**
     * Storefront badge palette: status => [text colour, background colour]. Inline-styled
     * (purge-proof) single source of truth for the shop datagrids and the
     * x-b2b::quote-status-badge component used on the shop view pages.
     */
    public const STATUS_COLORS = [
        self::STATUS_DRAFT => ['#3f3f46', '#f4f4f5'],
        self::STATUS_OPEN => ['#0044F2', '#e6edff'],
        self::STATUS_NEGOTIATION => ['#9a6700', '#fff3da'],
        self::STATUS_ACCEPTED => ['#1f7a33', '#e7f6ea'],
        self::STATUS_ORDERED => ['#060c3b', '#e8eaf2'],
        self::STATUS_COMPLETED => ['#1f7a33', '#e7f6ea'],
        self::STATUS_EXPIRED => ['#52525b', '#f4f4f5'],
        self::STATUS_REJECTED => ['#b42318', '#fde8e6'],
    ];

    /**
     * Returns the status label array.
     */
    public static function statusLabel()
    {
        return self::$statusLabel;
    }

    /**
     * Admin theme `label-*` class for a status (defaults to label-info if unknown).
     */
    public static function statusLabelClass(?string $status): string
    {
        return self::STATUS_LABEL_CLASSES[$status] ?? 'label-info';
    }

    /**
     * [text colour, background colour] for a storefront status badge.
     */
    public static function statusColor(?string $status): array
    {
        return self::STATUS_COLORS[$status] ?? ['#3f3f46', '#f4f4f5'];
    }

    /**
     * Inline-styled storefront status pill HTML — used by the shop datagrids so they match
     * the x-b2b::quote-status-badge component rendered on the shop view pages.
     */
    public static function statusBadge(?string $status, ?string $label = null): string
    {
        [$text, $bg] = self::statusColor($status);

        $label = $label ?? Str::title((string) $status);

        return '<span class="inline-flex items-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold"'
            .' style="color: '.$text.'; background-color: '.$bg.';">'.e($label).'</span>';
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
        return $this->belongsTo(Admin::class, 'agent_id');
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
