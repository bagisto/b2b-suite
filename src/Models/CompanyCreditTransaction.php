<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyCreditTransaction as CompanyCreditTransactionContract;
use Webkul\Sales\Models\OrderProxy;

class CompanyCreditTransaction extends Model implements CompanyCreditTransactionContract
{
    /**
     * Initial credit limit allocated to the company.
     */
    public const OPERATION_ALLOCATED = 'allocated';

    /**
     * Credit limit or settings were updated.
     */
    public const OPERATION_UPDATED = 'updated';

    /**
     * An order was charged to the credit (increases the outstanding balance).
     */
    public const OPERATION_PURCHASED = 'purchased';

    /**
     * An offline payment was recorded (decreases the outstanding balance).
     */
    public const OPERATION_REIMBURSED = 'reimbursed';

    /**
     * A completed order was refunded (decreases the outstanding balance).
     */
    public const OPERATION_REFUNDED = 'refunded';

    /**
     * A purchase was reversed because its order was cancelled.
     */
    public const OPERATION_REVERTED = 'reverted';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_credit_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_credit_id',
        'operation',
        'amount',
        'outstanding_balance_after',
        'available_credit_after',
        'credit_limit_after',
        'order_id',
        'reference',
        'comment',
        'actor_type',
        'actor_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:4',
        'outstanding_balance_after' => 'decimal:4',
        'available_credit_after' => 'decimal:4',
        'credit_limit_after' => 'decimal:4',
    ];

    /**
     * The credit account this entry belongs to.
     */
    public function companyCredit(): BelongsTo
    {
        return $this->belongsTo(CompanyCreditProxy::modelClass(), 'company_credit_id');
    }

    /**
     * The order tied to a purchase/refund/revert entry (if any).
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderProxy::modelClass(), 'order_id');
    }
}
