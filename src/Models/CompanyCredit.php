<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\B2BSuite\Contracts\CompanyCredit as CompanyCreditContract;
use Webkul\Customer\Models\CustomerProxy;

class CompanyCredit extends Model implements CompanyCreditContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_credits';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'credit_currency_code',
        'credit_limit',
        'outstanding_balance',
        'allow_exceed_limit',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'credit_limit' => 'decimal:4',
        'outstanding_balance' => 'decimal:4',
        'allow_exceed_limit' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * The company (a `customers` row with type = company) this credit belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'company_id');
    }

    /**
     * The append-only ledger of credit operations.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CompanyCreditTransactionProxy::modelClass(), 'company_credit_id');
    }

    /**
     * Available credit = limit - what is currently owed (may go negative when the
     * "allow exceed limit" flag let an order push past the limit).
     */
    public function getAvailableCreditAttribute(): float
    {
        return (float) $this->credit_limit - (float) $this->outstanding_balance;
    }
}
