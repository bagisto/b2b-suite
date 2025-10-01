<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Support\Facades\Event;
use Webkul\B2BSuite\Contracts\CustomerFlat as CustomerFlatContract;
use Webkul\Core\Eloquent\Repository;

class CustomerFlatRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return CustomerFlatContract::class;
    }

    /**
     * Create customer flat record.
     */
    public function create(array $data): CustomerFlatContract
    {
        Event::dispatch('customer.flat.create.before');

        $customerFlat = $this->model->create($data);

        Event::dispatch('customer.flat.create.after', $customerFlat);

        return $customerFlat;
    }

    /**
     * Update customer flat record.
     */
    public function update(array $data, $id, $attribute = 'id'): CustomerFlatContract
    {
        Event::dispatch('customer.flat.update.before', $id);

        $customerFlat = $this->find($id);

        $customerFlat->update($data);

        Event::dispatch('customer.flat.update.after', $customerFlat);

        return $customerFlat;
    }

    /**
     * Delete customer flat record.
     */
    public function delete($id): bool
    {
        Event::dispatch('customer.flat.delete.before', $id);

        $result = parent::delete($id);

        Event::dispatch('customer.flat.delete.after', $id);

        return $result;
    }

    /**
     * Get customer flat by customer ID and locale.
     */
    public function findByCustomerAndLocale(int $customerId, string $locale): ?CustomerFlatContract
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Get customer flat by customer ID, locale and channel.
     */
    public function findByCustomerLocaleAndChannel(int $customerId, string $locale, string $channel): ?CustomerFlatContract
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('locale', $locale)
            ->where('channel', $channel)
            ->first();
    }

    /**
     * Get all customer flat records for a customer.
     */
    public function getByCustomer(int $customerId)
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->get();
    }

    /**
     * Create or update customer flat record.
     */
    public function createOrUpdate(array $data): CustomerFlatContract
    {
        $customerFlat = $this->findByCustomerLocaleAndChannel(
            $data['customer_id'],
            $data['locale'],
            $data['channel']
        );

        if ($customerFlat) {
            return $this->update($data, $customerFlat->id);
        }

        return $this->create($data);
    }

    /**
     * Sync customer flat records for all locales and channels.
     */
    public function syncForCustomer(int $customerId, array $data): void
    {
        $locales = core()->getAllLocales();
        $channels = core()->getAllChannels();

        foreach ($locales as $locale) {
            foreach ($channels as $channel) {
                $flatData = array_merge($data, [
                    'customer_id' => $customerId,
                    'locale'      => $locale->code,
                    'channel'     => $channel->code,
                ]);

                $this->createOrUpdate($flatData);
            }
        }
    }
}
