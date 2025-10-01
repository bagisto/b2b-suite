<?php

namespace Webkul\B2BSuite\Helpers;

use Illuminate\Support\Facades\Schema;
use Webkul\B2BSuite\Repositories\CustomerFlatRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class FlatIndexer
{
    /**
     * Default batch size
     */
    protected const BATCH_SIZE = 100;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * Attribute codes that can be fill during flat creation.
     *
     * @var string[]
     */
    protected $fillableAttributeCodes = [
        'sku',
        'name',
        'price',
        'weight',
        'status',
    ];

    /**
     * @var array
     */
    protected $flatColumns = [];

    /**
     * Channels
     *
     * @var array
     */
    protected $channels = [];

    /**
     * Family Attributes
     *
     * @var array
     */
    protected $familyAttributes = [];

    /**
     * Create a new listener instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerFlatRepository $customerFlatRepository
    ) {
        $this->batchSize = self::BATCH_SIZE;

        $this->flatColumns = Schema::getColumnListing('customer_flat');
    }

    /**
     * Refresh customer flat indices
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
     * @return void
     */
    public function refresh($customer)
    {
        $customer = $this->customerRepository->find($customer->id);

        $this->updateOrCreate($customer);
    }

    /**
     * Creates customer flat
     *
     * @param  \Webkul\Customer\Contracts\Customer  $customer
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
                    $customerFlat = $this->customerFlatRepository->updateOrCreate([
                        'customer_id' => $customer->id,
                        'channel'     => $channel->code,
                        'locale'      => $locale->code,
                    ], [
                        'slug'  => $customer->slug,
                        'email' => $customer->email,
                        'phone' => $customer->phone,
                    ]);

                    foreach ($customerAttributes as $attribute) {
                        if (
                            ! in_array($attribute->code, $this->flatColumns)
                            || $attribute->code == 'slug'
                        ) {
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
                    $this->customerFlatRepository->deleteWhere([
                        'customer_id' => $customer->id,
                        'channel'     => $channel->code,
                    ]);
                }
            }
        }
    }
}
