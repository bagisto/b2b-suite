<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

use Illuminate\Support\Facades\DB;

/**
 * Seeds a company credit line for every demo company and records the opening "allocated"
 * transaction (outstanding balance starts at zero). The actual draw-down is recorded by the
 * purchase-order seeder, which charges these lines when it places "Pay By Credit" orders — so
 * each ledger stays consistent with the company's real orders.
 */
class DemoCompanyCreditSeeder extends DemoSeeder
{
    /**
     * Realistic credit-limit tiers (base currency).
     */
    protected const LIMIT_TIERS = [25000, 50000, 75000, 100000, 250000, 500000];

    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $now = $parameters['now'] ?? now()->toDateTimeString();
        $currency = $parameters['currency'] ?? core()->getBaseCurrencyCode();
        $salesRepId = DB::table('admins')->orderBy('id')->value('id');

        $this->demoCompanyIds()->chunk(1000)->each(function ($companyIds) use ($now, $currency, $salesRepId) {
            $creditRows = [];

            foreach ($companyIds as $companyId) {
                $creditRows[$companyId] = [
                    'company_id' => $companyId,
                    'credit_currency_code' => $currency,
                    'credit_limit' => $this->faker()->randomElement(self::LIMIT_TIERS),
                    'outstanding_balance' => 0,
                    'allow_exceed_limit' => $this->faker()->boolean(15),
                    'status' => $this->faker()->boolean(92),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->bulkInsert('b2b_company_credits', array_values($creditRows));

            /**
             * Record the opening allocation transaction for each credit line.
             */
            $creditIdByCompany = DB::table('b2b_company_credits')
                ->whereIn('company_id', $companyIds)
                ->pluck('id', 'company_id');

            $transactionRows = [];

            foreach ($creditRows as $companyId => $credit) {
                $limit = (float) $credit['credit_limit'];

                $transactionRows[] = [
                    'company_credit_id' => $creditIdByCompany[$companyId],
                    'operation' => 'allocated',
                    'amount' => $limit,
                    'outstanding_balance_after' => 0,
                    'available_credit_after' => $limit,
                    'credit_limit_after' => $limit,
                    'reference' => null,
                    'comment' => 'Initial credit line allocated.',
                    'actor_type' => 'admin',
                    'actor_id' => $salesRepId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $this->bulkInsert('b2b_company_credit_transactions', $transactionRows);
        });
    }
}
