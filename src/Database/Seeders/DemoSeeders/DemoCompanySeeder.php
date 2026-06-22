<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds demo companies — each a `customers` row of type `company` with a flattened
 * `b2b_company_flat` profile, the backing company-attribute values (so the edit form is
 * populated too), an Administrator + Buyer role, a self company link and a handful of member
 * sub-users. Companies are built in batches so the dependent rows (roles, members, pivots) can
 * read back their parents' ids without holding the whole data set in memory.
 */
class DemoCompanySeeder extends DemoSeeder
{
    /**
     * Company-attribute codes mirrored into `b2b_company_attribute_values` for the edit form.
     */
    protected const PROFILE_ATTRIBUTES = [
        'first_name', 'last_name', 'email', 'phone', 'business_name',
        'website_url', 'vat_tax_id', 'address', 'city', 'country', 'state', 'postcode',
    ];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $companyCount = max(1, (int) ($parameters['companies'] ?? 25));
        $membersMax = max(0, (int) ($parameters['members'] ?? 3));
        $locale = $parameters['locale'] ?? app()->getLocale();
        $channel = $parameters['channel'] ?? core()->getCurrentChannel()->code;
        $now = $parameters['now'] ?? now()->toDateTimeString();

        $groupId = DB::table('customer_groups')->where('code', 'general')->value('id');
        $salesRepId = DB::table('admins')->orderBy('id')->value('id');
        $attributeIds = DB::table('b2b_company_attributes')->pluck('id', 'code');
        $password = Hash::make('admin123');

        $companySeq = 0;
        $memberSeq = 0;

        while ($companySeq < $companyCount) {
            $batch = min(self::COMPANY_BATCH, $companyCount - $companySeq);

            /**
             * Build the company customer rows for this batch, keyed by their unique email so
             * the generated profile data can be re-attached once the ids are read back.
             */
            $profiles = [];
            $companyRows = [];

            for ($i = 0; $i < $batch; $i++) {
                $profile = $this->fakeCompany(++$companySeq);
                $profiles[$profile['email']] = $profile;

                $companyRows[] = [
                    'first_name' => $profile['first_name'],
                    'last_name' => $profile['last_name'],
                    'gender' => $profile['gender'],
                    'email' => $profile['email'],
                    'type' => 'company',
                    'phone' => $profile['phone'],
                    'status' => 1,
                    'password' => $password,
                    'api_token' => Str::random(80),
                    'customer_group_id' => $groupId,
                    'sales_rep_id' => $salesRepId,
                    'channel_id' => core()->getCurrentChannel()->id,
                    'is_verified' => 1,
                    'subscribed_to_news_letter' => 0,
                    'created_at' => $profile['created_at'],
                    'updated_at' => $now,
                ];
            }

            $this->bulkInsert('customers', $companyRows);

            $companyIdByEmail = DB::table('customers')
                ->whereIn('email', array_keys($profiles))
                ->pluck('id', 'email');

            $this->seedRoles($companyIdByEmail, $profiles, $now);
            $this->seedFlatAndValues($companyIdByEmail, $profiles, $attributeIds, $locale, $channel, $now);
            $this->seedMembers($companyIdByEmail, $profiles, $groupId, $password, $membersMax, $memberSeq, $now);
        }
    }

    /**
     * Create the Administrator + Buyer roles for each company, point the owner at the
     * Administrator role and self-link the company in the members pivot.
     */
    protected function seedRoles($companyIdByEmail, array $profiles, string $now): void
    {
        $roleRows = [];

        foreach ($companyIdByEmail as $companyId) {
            $roleRows[] = [
                'name' => 'Administrator',
                'description' => 'Full access to every company feature.',
                'permission_type' => 'all',
                'permissions' => null,
                'customer_id' => $companyId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $roleRows[] = [
                'name' => 'Buyer',
                'description' => 'Can raise quotes, quick orders and requisitions.',
                'permission_type' => 'custom',
                'permissions' => json_encode([
                    'quotes', 'quotes.view', 'purchase_orders', 'purchase_orders.view',
                    'requisitions', 'requisitions.create', 'quick_orders',
                ]),
                'customer_id' => $companyId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->bulkInsert('b2b_company_roles', $roleRows);

        /**
         * Read the freshly created roles back and assign the owner its Administrator role plus
         * a self company link (mirrors the admin "create company" flow).
         */
        $roles = DB::table('b2b_company_roles')
            ->whereIn('customer_id', $companyIdByEmail->values())
            ->get(['id', 'customer_id', 'name']);

        $pivotRows = [];

        foreach ($roles->where('name', 'Administrator') as $role) {
            DB::table('customers')->where('id', $role->customer_id)->update(['company_role_id' => $role->id]);

            $pivotRows[] = ['customer_id' => $role->customer_id, 'company_id' => $role->customer_id];
        }

        $this->bulkInsert('b2b_customer_companies', $pivotRows);
    }

    /**
     * Write the flattened profile row plus the backing attribute values for each company.
     */
    protected function seedFlatAndValues($companyIdByEmail, array $profiles, $attributeIds, string $locale, string $channel, string $now): void
    {
        $flatRows = [];
        $valueRows = [];

        foreach ($profiles as $email => $profile) {
            $companyId = $companyIdByEmail[$email] ?? null;

            if (! $companyId) {
                continue;
            }

            $flatRows[] = [
                'customer_id' => $companyId,
                'locale' => $locale,
                'channel' => $channel,
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'email' => $profile['email'],
                'phone' => $profile['phone'],
                'business_name' => $profile['business_name'],
                'website_url' => $profile['website_url'],
                'vat_tax_id' => $profile['vat_tax_id'],
                'address' => $profile['address'],
                'city' => $profile['city'],
                'country' => $profile['country'],
                'state' => $profile['state'],
                'postcode' => $profile['postcode'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            foreach (self::PROFILE_ATTRIBUTES as $code) {
                if (empty($attributeIds[$code]) || ! isset($profile[$code])) {
                    continue;
                }

                $valueRows[] = [
                    'locale' => $locale,
                    'channel' => $channel,
                    'text_value' => $profile[$code],
                    'customer_id' => $companyId,
                    'company_attribute_id' => $attributeIds[$code],
                    'unique_id' => implode('|', [$channel, $locale, $companyId, $attributeIds[$code]]),
                ];
            }
        }

        $this->bulkInsert('b2b_company_flat', $flatRows);
        $this->bulkInsert('b2b_company_attribute_values', $valueRows);
    }

    /**
     * Create a random number of member sub-users per company, each on the Buyer role and
     * linked through the members pivot.
     */
    protected function seedMembers($companyIdByEmail, array $profiles, $groupId, string $password, int $membersMax, int &$memberSeq, string $now): void
    {
        if ($membersMax < 1) {
            return;
        }

        $buyerRoleId = DB::table('b2b_company_roles')
            ->whereIn('customer_id', $companyIdByEmail->values())
            ->where('name', 'Buyer')
            ->pluck('id', 'customer_id');

        $memberRows = [];
        $memberOwner = [];

        foreach ($companyIdByEmail as $companyId) {
            foreach (range(1, random_int(1, $membersMax)) as $ignored) {
                $first = $this->faker()->firstName();
                $last = $this->faker()->lastName();
                $email = 'member-'.(++$memberSeq).'@'.self::DEMO_DOMAIN;

                $memberOwner[$email] = $companyId;

                $memberRows[] = [
                    'first_name' => $first,
                    'last_name' => $last,
                    'gender' => $this->faker()->randomElement(['Male', 'Female', 'Other']),
                    'email' => $email,
                    'type' => 'user',
                    'phone' => '+1'.str_pad((string) (3000000000 + $memberSeq), 10, '0', STR_PAD_LEFT),
                    'status' => 1,
                    'password' => $password,
                    'api_token' => Str::random(80),
                    'customer_group_id' => $groupId,
                    'company_role_id' => $buyerRoleId[$companyId] ?? null,
                    'channel_id' => core()->getCurrentChannel()->id,
                    'is_verified' => 1,
                    'subscribed_to_news_letter' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->bulkInsert('customers', $memberRows);

        /**
         * Link each member to its company through the pivot.
         */
        $memberIdByEmail = DB::table('customers')
            ->whereIn('email', array_keys($memberOwner))
            ->pluck('id', 'email');

        $pivotRows = [];

        foreach ($memberOwner as $email => $companyId) {
            if (isset($memberIdByEmail[$email])) {
                $pivotRows[] = ['customer_id' => $memberIdByEmail[$email], 'company_id' => $companyId];
            }
        }

        $this->bulkInsert('b2b_customer_companies', $pivotRows);
    }

    /**
     * Generate a realistic company profile for the given sequence number.
     */
    protected function fakeCompany(int $seq): array
    {
        $faker = $this->faker();

        $business = $faker->company().' '.$faker->companySuffix();

        return [
            'business_name' => $business,
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'gender' => $faker->randomElement(['Male', 'Female', 'Other']),
            'email' => Str::slug(Str::words($business, 2, '')).'-'.$seq.'@'.self::DEMO_DOMAIN,
            'phone' => '+1'.str_pad((string) (2000000000 + $seq), 10, '0', STR_PAD_LEFT),
            'website_url' => 'https://'.Str::slug($business).'.example.com',
            'vat_tax_id' => 'VAT'.$faker->numerify('#########'),
            'address' => $faker->streetAddress(),
            'city' => $faker->city(),
            'state' => $faker->stateAbbr(),
            'country' => 'US',
            'postcode' => $faker->postcode(),
            'created_at' => $faker->dateTimeBetween('-18 months', '-1 day')->format('Y-m-d H:i:s'),
        ];
    }
}
