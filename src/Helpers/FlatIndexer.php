<?php

namespace Webkul\B2BSuite\Helpers;

use Illuminate\Support\Facades\Schema;
use Webkul\B2BSuite\Repositories\CompanyFlatRepository;
use Webkul\Customer\Contracts\Customer;
use Webkul\Customer\Repositories\CustomerRepository;

class FlatIndexer
{
    /**
     * @var array
     */
    protected $flatColumns = [];

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CompanyFlatRepository $companyFlatRepository
    ) {
        $this->flatColumns = Schema::getColumnListing('company_flat');
    }

    /**
     * Refresh customer flat indices
     *
     * @param  Customer  $customer
     * @return void
     */
    public function refresh($customer)
    {
        $customer = $this->customerRepository->find($customer->id);

        /**
         * Only the company owner carries company attribute data. Sub-users (and plain
         * customers) share their company's details, so they must not get their own
         * company_flat row — drop any stale one if the record is not a company.
         */
        if (($customer->type ?? null) !== 'company') {
            $this->companyFlatRepository->deleteWhere(['customer_id' => $customer->id]);

            return;
        }

        $this->updateOrCreate($customer);
    }

    /**
     * Creates customer flat
     *
     * @param  Customer  $customer
     * @return void
     */
    public function updateOrCreate($customer)
    {
        $channelIds[] = $customer->channel->id;

        if (empty($channelIds)) {
            $channelIds[] = core()->getDefaultChannel()->id;
        }

        $customerAttributes = $customer->custom_attributes()->get();

        $attributeValues = $customer->attribute_values()->get();

        foreach (core()->getAllChannels() as $channel) {
            if (in_array($channel->id, $channelIds)) {
                foreach ($channel->locales as $locale) {
                    $customerFlat = $this->companyFlatRepository->updateOrCreate([
                        'customer_id' => $customer->id,
                        'channel' => $channel->code,
                        'locale' => $locale->code,
                    ], [
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);

                    foreach ($customerAttributes as $attribute) {
                        if (! in_array($attribute->code, $this->flatColumns)) {
                            continue;
                        }

                        $customerAttributeValues = $attributeValues->where('company_attribute_id', $attribute->id);

                        if ($attribute->value_per_channel) {
                            if ($attribute->value_per_locale) {
                                $customerAttributeValues = $customerAttributeValues
                                    ->where('channel', $channel->code)
                                    ->where('locale', $locale->code);
                            } else {
                                $customerAttributeValues = $customerAttributeValues->where('channel', $channel->code);
                            }
                        } else {
                            if ($attribute->value_per_locale) {
                                $customerAttributeValues = $customerAttributeValues->where('locale', $locale->code);
                            }
                        }

                        $customerAttributeValue = $customerAttributeValues->first();

                        $customerFlat->{$attribute->code} = $customerAttributeValue[$attribute->column_name] ?? null;
                    }

                    $customerFlat->save();
                }
            } else {
                if (request()->route()?->getName() == 'admin.customer.customers.update') {
                    $this->companyFlatRepository->deleteWhere([
                        'customer_id' => $customer->id,
                        'channel' => $channel->code,
                    ]);
                }
            }
        }
    }
}
