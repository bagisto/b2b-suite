<?php

namespace Webkul\B2BSuite\Database\Seeders\DemoSeeders;

/**
 * Seeds demo quotations (`b2b_customer_quotes` with `state = quotation`) — the RFQ side, with a
 * spread of lifecycle statuses, line items and a buyer/agent message thread per company.
 */
class DemoQuotationSeeder extends AbstractQuoteSeeder
{
    /**
     * Run the seeder.
     *
     * @return void
     */
    public function run(array $parameters = [])
    {
        $this->seedQuotes(
            $parameters,
            'quotation',
            ['draft', 'open', 'negotiation', 'accepted', 'rejected', 'expired'],
            'QT',
            max(0, (int) ($parameters['quotations'] ?? 4)),
        );
    }
}
