<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CompanyCreditTransactionDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder. Scoped to the logged-in buyer's company credit ledger.
     */
    public function prepareQueryBuilder()
    {
        $customer = auth()->guard('customer')->user();

        $companyId = DB::table('customer_companies')
            ->where('customer_id', $customer->id)
            ->value('company_id') ?? $customer->id;

        $creditId = DB::table('company_credits')
            ->where('company_id', $companyId)
            ->value('id');

        $queryBuilder = DB::table('company_credit_transactions')
            ->leftJoin('orders', 'company_credit_transactions.order_id', '=', 'orders.id')
            ->where('company_credit_transactions.company_credit_id', $creditId)
            ->select(
                'company_credit_transactions.id',
                'company_credit_transactions.operation',
                // Raw copy: the operation column's closure overwrites `operation` with badge
                // HTML, so later closures read this untouched value.
                'company_credit_transactions.operation as operation_raw',
                'company_credit_transactions.amount',
                'company_credit_transactions.available_credit_after',
                'company_credit_transactions.order_id',
                'company_credit_transactions.reference',
                'company_credit_transactions.comment',
                'company_credit_transactions.created_at',
                'orders.increment_id as order_increment_id'
            );

        $this->addFilter('operation', 'company_credit_transactions.operation');
        $this->addFilter('amount', 'company_credit_transactions.amount');
        $this->addFilter('created_at', 'company_credit_transactions.created_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('b2b::app.shop.companies.account.credit.date'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at, 'd M Y, h:i A'),
        ]);

        $this->addColumn([
            'index' => 'operation',
            'label' => trans('b2b::app.shop.companies.account.credit.operation'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => collect([
                'allocated', 'purchased', 'reimbursed', 'refunded', 'reverted',
            ])->map(fn ($operation) => [
                'label' => trans('b2b::app.shop.companies.account.credit.operations.'.$operation),
                'value' => $operation,
            ])->toArray(),
            'sortable' => true,
            'closure' => function ($row) {
                $label = trans('b2b::app.shop.companies.account.credit.operations.'.$row->operation);

                $class = match ($row->operation) {
                    'purchased' => 'label-pending',
                    'reimbursed', 'refunded', 'reverted' => 'label-active',
                    default => 'label-info',
                };

                return '<span class="'.$class.' inline-block whitespace-nowrap">'.$label.'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'amount',
            'label' => trans('b2b::app.shop.companies.account.credit.amount'),
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                /**
                 * From the available-credit perspective: a purchase consumes credit (− red);
                 * an allocation, payment, refund or reversal frees / grants credit (+ green).
                 */
                $isDebit = $row->operation_raw === 'purchased';

                $color = $isDebit ? 'text-red-600' : 'text-green-700';

                $sign = $isDebit ? '− ' : '+ ';

                return '<span class="font-medium '.$color.'">'.$sign.core()->formatBasePrice($row->amount).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'available_credit_after',
            'label' => trans('b2b::app.shop.companies.account.credit.balance'),
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => fn ($row) => core()->formatBasePrice($row->available_credit_after),
        ]);

        $this->addColumn([
            'index' => 'details',
            'label' => trans('b2b::app.shop.companies.account.credit.details'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $parts = [];

                if ($row->order_id) {
                    $parts[] = '<a href="'.route('shop.customers.account.orders.view', $row->order_id).'" class="text-blue-600 hover:underline">'
                        .trans('b2b::app.shop.companies.account.credit.order').' #'.($row->order_increment_id ?? $row->order_id).'</a>';
                }

                if ($row->reference) {
                    $parts[] = '<span class="text-zinc-600">'.e($row->reference).'</span>';
                }

                if ($row->comment) {
                    $parts[] = '<span class="italic text-zinc-400">'.e($row->comment).'</span>';
                }

                return $parts ? implode('<br>', $parts) : '<span class="text-zinc-400">—</span>';
            },
        ]);
    }

    /**
     * Prepare actions — read-only ledger, no row actions.
     */
    public function prepareActions()
    {
        //
    }
}
