<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\B2BSuite\Contracts\CompanyCreditTransaction as CompanyCreditTransactionContract;
use Webkul\Sales\Models\OrderProxy;

class CompanyCreditTransaction extends Model implements CompanyCreditTransactionContract
{
    protected $table = 'company_credit_transactions';

    /**
     * Ledger operations. The first four constants increase what the company owes
     * (purchases); reimbursed/refunded/reverted decrease it.
     */
    public const OPERATION_ALLOCATED = 'allocated';

    public const OPERATION_UPDATED = 'updated';

    public const OPERATION_PURCHASED = 'purchased';

    public const OPERATION_REIMBURSED = 'reimbursed';

    public const OPERATION_REFUNDED = 'refunded';

    public const OPERATION_REVERTED = 'reverted';

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
