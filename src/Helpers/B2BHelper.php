<?php

namespace Webkul\B2BSuite\Helpers;

class B2BHelper
{
    /**
     * Get company by customer ID
     *
     * @param  int  $customerId
     * @return \Webkul\B2BSuite\Models\Company|null
     */
    public function getCompanyByCustomer($customerId)
    {
        dd($customerId);
        $companyUser = app(\Webkul\B2BSuite\Models\CompanyUser::class)
            ->where('user_id', $customerId)
            ->first();

        return $companyUser ? $companyUser->company : null;
    }

    /**
     * Check if customer belongs to any company
     *
     * @param  int  $customerId
     * @return bool
     */
    public function isB2BCustomer($customerId)
    {
        return app(\Webkul\B2BSuite\Models\CompanyUser::class)
            ->where('user_id', $customerId)
            ->exists();
    }

    /**
     * Get company users
     *
     * @param  int  $companyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompanyUsers($companyId)
    {
        return app(\Webkul\B2BSuite\Models\CompanyUser::class)
            ->where('company_id', $companyId)
            ->with('user')
            ->get();
    }

    /**
     * Assign customer to company
     *
     * @param  int  $customerId
     * @param  int  $companyId
     * @param  string  $role
     * @return \Webkul\B2BSuite\Models\CompanyUser
     */
    public function assignCustomerToCompany($customerId, $companyId, $role = 'member')
    {
        return app(\Webkul\B2BSuite\Models\CompanyUser::class)->create([
            'user_id'    => $customerId,
            'company_id' => $companyId,
            'role'       => $role,
            'status'     => true,
        ]);
    }

    /**
     * Remove customer from company
     *
     * @param  int  $customerId
     * @param  int  $companyId
     * @return bool
     */
    public function removeCustomerFromCompany($customerId, $companyId)
    {
        return app(\Webkul\B2BSuite\Models\CompanyUser::class)
            ->where('user_id', $customerId)
            ->where('company_id', $companyId)
            ->delete();
    }
}
