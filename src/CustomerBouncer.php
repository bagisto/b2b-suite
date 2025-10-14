<?php

namespace Webkul\B2BSuite;

use Webkul\B2BSuite\Repositories\CompanyRoleRepository;

class CustomerBouncer
{
    /**
     * Permission types
     */
    public const PERMISSION_TYPE_ALL = 'all';

    public const PERMISSION_TYPE_CUSTOM = 'custom';

    public function __construct(protected CompanyRoleRepository $roleRepo) {}

    /**
     * Check if the current logged-in customer has permission for a given key.
     */
    public function hasPermission(string $permission): bool
    {
        $customer = auth()->guard('customer')->user();

        if ($customer->type === 'company') {
            $role = $this->roleRepo->findWhere(['customer_id' => $customer->id])->first();
        } else {
            $role = $this->roleRepo->find($customer->company_role_id);
        }

        if (! $role) {
            return false;
        }

        if ($role->permission_type === self::PERMISSION_TYPE_ALL) {
            return true;
        }

        if ($role->permission_type === self::PERMISSION_TYPE_CUSTOM) {
            $permissions = $role->permissions;

            if (is_string($permissions)) {
                $permissions = json_decode($permissions, true) ?: [];
            }

            if (! is_array($permissions)) {
                $permissions = ['account'];
            }

            $permissions = array_map(fn ($perm) => 'account.'.$perm, $permissions);
            array_unshift($permissions, 'account');

            $hasPermission = in_array($permission, $permissions);

            return $hasPermission;
        }

        return false;
    }

    /**
     * Abort unauthorized actions.
     */
    public static function allow(string $permission): void
    {
        $instance = app(self::class);

        if (! $instance->hasPermission($permission)) {
            abort(401, 'Unauthorized action.');
        }
    }
}
