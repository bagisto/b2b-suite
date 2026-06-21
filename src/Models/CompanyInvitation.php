<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Customer\Models\CustomerProxy;

class CompanyInvitation extends Model
{
    /**
     * Invitation sent, awaiting the invitee's response.
     */
    public const STATUS_PENDING = 'pending';

    /**
     * Invitation accepted — the invitee joined the company.
     */
    public const STATUS_ACCEPTED = 'accepted';

    /**
     * Invitation declined by the invitee.
     */
    public const STATUS_DECLINED = 'declined';

    /**
     * Invitation revoked by the company before it was answered.
     */
    public const STATUS_REVOKED = 'revoked';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'b2b_company_invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'email',
        'company_role_id',
        'invited_by',
        'token',
        'status',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * The company (customer of type "company") this invitation belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(CustomerProxy::modelClass(), 'company_id');
    }

    /**
     * The role granted to the invitee on acceptance.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(CompanyRoleProxy::modelClass(), 'company_role_id');
    }

    /**
     * Whether the invitation is still actionable (pending and not expired).
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
