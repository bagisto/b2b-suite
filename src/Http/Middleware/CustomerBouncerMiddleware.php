<?php

namespace Webkul\B2BSuite\Http\Middleware;

use Closure;
use Webkul\B2BSuite\CustomerBouncer;

class CustomerBouncerMiddleware
{
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

        $roles = b2b_acl()->getRoles();

        if (isset($roles[$routeName])) {
            $aclKey = 'account.'.$roles[$routeName];

            try {
                CustomerBouncer::allow($aclKey);
            } catch (\Exception $e) {
                abort(401, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
