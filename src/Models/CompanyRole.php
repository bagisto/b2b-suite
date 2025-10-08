<?php

namespace Webkul\B2BSuite\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\B2BSuite\Contracts\CompanyRole as CompanyRoleContract;
use Webkul\Customer\Models\CustomerProxy;

class CompanyRole extends Model implements CompanyRoleContract
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'permission_type',
        'permissions',
        'customer_id',
    ];

    /**
     * The attributes that are castable.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the customers.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customers()
    {
        return $this->hasMany(CustomerProxy::modelClass());
    }
}
