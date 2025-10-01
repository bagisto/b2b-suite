<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\DataGrid\DataGrid;

class CustomerPurchaseOrderDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'quote_id';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('customer_quotes')
            ->distinct()
            ->leftJoin('customers as company', 'customer_quotes.company_id', '=', 'company.id')
            ->leftJoin('customers as customer', 'customer_quotes.customer_id', '=', 'customer.id')
            ->addSelect(
                'customer_quotes.id as quote_id',
                'customer_quotes.po_number',
                'customer_quotes.name',
                'company.email as company_email',
                'customer_quotes.base_total',
                'customer_quotes.negotiated_total',
                'customer_quotes.state',
                'customer_quotes.status',
                'customer_quotes.order_date',
                'customer_quotes.expected_arrival_date',
                'customer_quotes.expiration_date',
                'customer_quotes.order_id',
                'customer_quotes.created_at',
                'customer_quotes.updated_at'
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name) as company_name'))
            ->where('customer_quotes.soft_deleted', 0)
            ->where('customer_quotes.state', CustomerQuote::STATE_PURCHASE_ORDER)
            ->whereIn('customer_quotes.status', [
                CustomerQuote::STATUS_ORDERED,
                CustomerQuote::STATUS_COMPLETED,
            ])
            ->where('customer_quotes.customer_id', auth()->guard('customer')->user()->id);

        $this->addFilter('po_number', 'customer_quotes.po_number');
        $this->addFilter('name', 'customer_quotes.name');
        $this->addFilter('state', 'customer_quotes.state');
        $this->addFilter('status', 'customer_quotes.status');
        $this->addFilter('base_total', 'customer_quotes.base_total');
        $this->addFilter('negotiated_total', 'customer_quotes.negotiated_total');
        $this->addFilter('company_name', DB::raw('CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name)'));
        $this->addFilter('created_at', 'customer_quotes.created_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'po_number',
            'label'      => trans('b2b_suite::app.shop.customers.account.purchase-orders.index.datagrid.id'),
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'company_name',
            'label'      => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.company'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'base_total',
            'label'      => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.base_total'),
            'type'       => 'decimal',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return core()->formatPrice($row->base_total, core()->getCurrentCurrencyCode());
            },
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.status'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.ordered'),
                    'value' => CustomerQuote::STATUS_ORDERED,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.completed'),
                    'value' => CustomerQuote::STATUS_COMPLETED,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case CustomerQuote::STATUS_ORDERED:
                        return '<p class="label-active">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.ordered').'</p>';

                    case CustomerQuote::STATUS_COMPLETED:
                        return '<p class="label-canceled">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.completed').'</p>';
                }
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.created-at'),
            'type'            => 'datetime',
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'edit',
            'icon'   => 'icon-eye',
            'title'  => trans('b2b_suite::app.admin.quotes.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('shop.customers.account.purchase_orders.view', $row->quote_id);
            },
        ]);
    }
}
