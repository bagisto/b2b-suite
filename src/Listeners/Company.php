<?php

namespace Webkul\B2BSuite\Listeners;

use Webkul\B2BSuite\Helpers\CompanyCatalog;
use Webkul\B2BSuite\Helpers\CreditManager;
use Webkul\B2BSuite\Helpers\FlatIndexer;
use Webkul\Customer\Contracts\Customer;
use Webkul\Customer\Repositories\CustomerRepository;

class Company
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected FlatIndexer $flatIndexer,
        protected CompanyCatalog $companyCatalog,
        protected CreditManager $creditManager,
    ) {}

    /**
     * Provision a (disabled, zero-limit) company credit account when a company is created
     * from the admin or registered from the storefront. The admin enables it and sets a
     * limit later from the Company Credit settings.
     *
     * @param  Customer  $customer
     * @return void
     */
    public function provisionCompanyCredit($customer)
    {
        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            return;
        }

        if (($customer->type ?? null) !== 'company') {
            return;
        }

        $this->creditManager->getOrCreate($customer->id);
    }

    /**
     * Update or create customer indices.
     *
     * @param  Customer  $customer
     * @return void
     */
    public function afterUpdate($customer)
    {
        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            return;
        }

        $data = request()->all();

        if (isset($data['company_list'])) {
            $companyIds = $data['company_ids'] ?? [];

            $customer->companies()->sync($companyIds);

            $customer->type = 'user';

            $customer->save();

            $this->inheritCompanyCatalogGroup($customer, $companyIds);
        }

        $this->flatIndexer->refresh($customer);
    }

    /**
     * Inherit the company-catalog customer group from the member's company (if any).
     *
     * @param  Customer  $customer
     */
    protected function inheritCompanyCatalogGroup($customer, array $companyIds): void
    {
        $companyId = current($companyIds);

        if (! $companyId) {
            return;
        }

        $company = $this->customerRepository->find($companyId);

        if (! $company?->company_catalog_id) {
            return;
        }

        $catalog = $this->companyCatalog->resolveByGroupId($company->customer_group_id)
            ?? $company->companyCatalog;

        $groupId = $catalog?->customer_group_id ?? $company->customer_group_id;

        if (
            $groupId
            && $customer->customer_group_id != $groupId
        ) {
            $customer->customer_group_id = $groupId;

            $customer->save();
        }
    }
}
