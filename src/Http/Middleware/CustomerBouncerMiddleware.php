<?php

namespace Webkul\B2BSuite\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Webkul\B2BSuite\CustomerBouncer;
use Webkul\B2BSuite\Repositories\CompanyRoleRepository;

class CustomerBouncerMiddleware
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected CompanyRoleRepository $roleRepo) {}

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $customer = auth()->guard('customer')->user();
        $routeName = $request->route()->getName();

        if (! $customer) {
            return redirect()->route('customer.session.index');
        }

        $roles = b2b_suite_acl()->getRoles();

        if (isset($roles[$routeName])) {
            $aclKey = $roles[$routeName];

            CustomerBouncer::allow($aclKey);
        }

        return $next($request);
    }

    /**
     * Check if customer has empty permissions.
     */
    protected function isPermissionsEmpty(): bool
    {
        $customer = auth()->guard('customer')->user();

        if (! $customer) {
            return true;
        }

        $role = $this->roleRepo
            ->findWhere(['customer_id' => $customer->id])
            ->first();

        if (! $role) {
            return false;
        }

        if ($role->permission_type === 'all') {
            return false;
        }

        if (
            $role->permission_type !== 'all' 
            && empty($role->permissions)
        ) {
            return true;
        }

        $this->checkIfAuthorized();

        return false;
    }

    /**
     * Check authorization based on ACL.
     */
    protected function checkIfAuthorized(): void
    {
        $roles = b2b_suite_acl()->getRoles();
        $currentRoute = Route::currentRouteName();

        if (isset($roles[$currentRoute])) {
            $aclKey = $roles[$currentRoute];

            CustomerBouncer::allow($aclKey);
        }
    }
}
