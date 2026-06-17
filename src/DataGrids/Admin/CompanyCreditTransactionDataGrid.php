<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

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
     * The credit account whose ledger is being listed. Set by the controller before
     * process() so the grid stays scoped to a single company.
     */
    public ?int $companyCreditId = null;

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('company_credit_transactions')
            ->leftJoin('orders', 'company_credit_transactions.order_id', '=', 'orders.id')
            ->where('company_credit_transactions.company_credit_id', $this->companyCreditId)
            ->select(
                'company_credit_transactions.id',
                'company_credit_transactions.operation',
                // Raw copy of the operation: the operation column's closure overwrites
                // `operation` with badge HTML, so later closures read this untouched value.
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
            'label' => trans('b2b::app.admin.companies.credit.date'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
            'closure' => fn ($row) => core()->formatDate($row->created_at, 'd M Y, h:i A'),
        ]);

        $this->addColumn([
            'index' => 'operation',
            'label' => trans('b2b::app.admin.companies.credit.operation'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => collect([
                'allocated', 'purchased', 'reimbursed', 'refunded', 'reverted',
            ])->map(fn ($operation) => [
                'label' => trans('b2b::app.admin.companies.credit.operations.'.$operation),
                'value' => $operation,
            ])->toArray(),
            'sortable' => true,
            'closure' => function ($row) {
                $label = trans('b2b::app.admin.companies.credit.operations.'.$row->operation);

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
            'label' => trans('b2b::app.admin.companies.credit.amount'),
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                /**
                 * Shown from the available-credit perspective (matching the running Balance
                 * column): a purchase consumes credit (− red); an allocation, payment, refund
                 * or reversal frees / grants credit (+ green).
                 */
                $isDebit = $row->operation_raw === 'purchased';

                $color = $isDebit ? 'text-red-600' : 'text-green-600';

                $sign = $isDebit ? '− ' : '+ ';

                return '<span class="font-medium '.$color.'">'.$sign.core()->formatBasePrice($row->amount).'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'available_credit_after',
            'label' => trans('b2b::app.admin.companies.credit.balance-after'),
            'type' => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => fn ($row) => core()->formatBasePrice($row->available_credit_after),
        ]);

        $this->addColumn([
            'index' => 'details',
            'label' => trans('b2b::app.admin.companies.credit.details'),
            'type' => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
            'closure' => function ($row) {
                $parts = [];

                if ($row->order_id) {
                    $parts[] = '<a href="'.route('admin.sales.orders.view', $row->order_id).'" class="text-blue-600 hover:underline">'
                        .trans('b2b::app.admin.companies.credit.order').' #'.($row->order_increment_id ?? $row->order_id).'</a>';
                }

                if ($row->reference) {
                    $parts[] = '<span class="text-gray-600 dark:text-gray-300">'.e($row->reference).'</span>';
                }

                if ($row->comment) {
                    $parts[] = '<span class="italic text-gray-400">'.e($row->comment).'</span>';
                }

                return $parts ? implode('<br>', $parts) : '<span class="text-gray-400">—</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        // Read-only audit ledger — no row actions.
    }
}
