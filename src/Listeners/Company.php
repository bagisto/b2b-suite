<?php

namespace Webkul\B2BSuite\Listeners;

use Webkul\B2BSuite\Helpers\FlatIndexer;
use Webkul\Customer\Contracts\Customer;

class Company
{
    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(protected FlatIndexer $flatIndexer) {}

    /**
     * Update or create customer indices
     *
     * @param  Customer  $customer
     * @return void
     */
    public function afterUpdate($customer)
    {
        if (! (bool) core()->getConfigData('b2b_suite.general.settings.active')) {
            return;
        }

        $data = request()->all();

        if (isset($data['company_list'])) {
            $companyIds = $data['company_ids'] ?? [];

            $customer->companies()->sync($companyIds);

            $customer->type = 'user';

            $customer->save();
        }

        $this->flatIndexer->refresh($customer);
    }
}
