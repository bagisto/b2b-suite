<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\DataGrid\DataGrid;

class CustomerQuoteDataGrid extends DataGrid
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
        $customer = auth()->guard('customer')->user();

        $companyId = DB::table('customer_companies')
            ->where('customer_id', $customer->id)
            ->value('company_id') ?? $customer->id;

        $queryBuilder = DB::table('customer_quotes')
            ->distinct()
            ->leftJoin('customers as company', 'customer_quotes.company_id', '=', 'company.id')
            ->leftJoin('customers as customer', 'customer_quotes.customer_id', '=', 'customer.id')
            ->addSelect(
                'customer_quotes.id as quote_id',
                'customer_quotes.quotation_number',
                'customer_quotes.name',
                'company.email as company_email',
                'customer_quotes.base_total',
                'customer_quotes.negotiated_total',
                'customer_quotes.state',
                'customer_quotes.status',
                'customer_quotes.order_date',
                'customer_quotes.expected_arrival_date',
                'customer_quotes.expiration_date',
                'customer_quotes.created_at',
                'customer_quotes.updated_at'
            )
            ->addSelect(DB::raw('CONCAT(' . $tablePrefix . 'company.first_name, " ", ' . $tablePrefix . 'company.last_name) as company_name'))
            ->where('customer_quotes.soft_deleted', 0)
            ->where('customer_quotes.state', CustomerQuote::STATE_QUOTATION)
            ->whereIn('customer_quotes.status', [
                CustomerQuote::STATUS_DRAFT,
                CustomerQuote::STATUS_OPEN,
                CustomerQuote::STATUS_NEGOTIATION,
                CustomerQuote::STATUS_ACCEPTED,
                CustomerQuote::STATUS_REJECTED,
            ])
            ->where('customer_quotes.company_id', $companyId);

        $this->addFilter('quotation_number', 'customer_quotes.quotation_number');
        $this->addFilter('name', 'customer_quotes.name');
        $this->addFilter('state', 'customer_quotes.state');
        $this->addFilter('status', 'customer_quotes.status');
        $this->addFilter('base_total', 'customer_quotes.base_total');
        $this->addFilter('negotiated_total', 'customer_quotes.negotiated_total');
        $this->addFilter('company_name', DB::raw('CONCAT(' . $tablePrefix . 'company.first_name, " ", ' . $tablePrefix . 'company.last_name)'));
        $this->addFilter('created_at', 'customer_quotes.created_at');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'quotation_number',
            'label'      => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.quote-id'),
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
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.draft'),
                    'value' => CustomerQuote::STATUS_DRAFT,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.open'),
                    'value' => CustomerQuote::STATUS_OPEN,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.accepted'),
                    'value' => CustomerQuote::STATUS_ACCEPTED,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.negotiation'),
                    'value' => CustomerQuote::STATUS_NEGOTIATION,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.expired'),
                    'value' => CustomerQuote::STATUS_EXPIRED,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.rejected'),
                    'value' => CustomerQuote::STATUS_REJECTED,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                switch ($row->status) {
                    case CustomerQuote::STATUS_DRAFT:
                        return '<p class="label-info">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.draft').'</p>';

                    case CustomerQuote::STATUS_OPEN:
                        return '<p class="label-pending">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.open').'</p>';

                    case CustomerQuote::STATUS_ACCEPTED:
                        return '<p class="label-processing">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.accepted').'</p>';

                    case CustomerQuote::STATUS_NEGOTIATION:
                        return '<p class="label-closed">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.negotiation').'</p>';

                    case CustomerQuote::STATUS_EXPIRED:
                        return '<p class="label-canceled">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.expired').'</p>';

                    case CustomerQuote::STATUS_REJECTED:
                        return '<p class="label-canceled">'.trans('b2b_suite::app.shop.customers.account.quotes.index.datagrid.rejected').'</p>';
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
                return route('shop.customers.account.quotes.view', $row->quote_id);
            },
        ]);
    }
}
