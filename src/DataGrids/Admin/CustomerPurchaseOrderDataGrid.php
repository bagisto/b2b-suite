<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
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
            ->leftJoin('admins as agent', 'customer_quotes.agent_id', '=', 'agent.id')
            ->addSelect(
                'customer_quotes.id as quote_id',
                'customer_quotes.po_number',
                'customer_quotes.name',
                'customer.email as customer_email',
                'company.email as company_email',
                'agent.name as agent_name',
                'customer_quotes.base_total',
                'customer_quotes.negotiated_total',
                'customer_quotes.status',
                'customer_quotes.soft_deleted',
                'customer_quotes.created_at',
                'customer_quotes.expiration_date'
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'customer.first_name, " ", '.$tablePrefix.'customer.last_name) as customer_name'))
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name) as company_name'))
            ->where('customer_quotes.state', CustomerQuote::STATE_PURCHASE_ORDER)
            ->whereIn('customer_quotes.status', [
                CustomerQuote::STATUS_ORDERED,
                CustomerQuote::STATUS_COMPLETED,
            ]);

        $this->addFilter('po_number', 'customer_quotes.po_number');
        $this->addFilter('name', 'customer_quotes.name');
        $this->addFilter('status', 'customer_quotes.status');
        $this->addFilter('soft_deleted', 'customer_quotes.soft_deleted');
        $this->addFilter('base_total', 'customer_quotes.base_total');
        $this->addFilter('negotiated_total', 'customer_quotes.negotiated_total');
        $this->addFilter('customer_name', DB::raw('CONCAT('.$tablePrefix.'customer.first_name, " ", '.$tablePrefix.'customer.last_name)'));
        $this->addFilter('company_name', DB::raw('CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name)'));
        $this->addFilter('agent_name', 'agent.name');
        $this->addFilter('created_at', 'customer_quotes.created_at');
        $this->addFilter('expiration_date', 'customer_quotes.expiration_date');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'po_number',
            'label'      => trans('b2b_suite::app.admin.purchase-orders.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'company_name',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.company'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'company_email',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.company-email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'customer_name',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.customer'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'customer_email',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.customer-email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'agent_name',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.agent'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'base_total',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.base_total'),
            'type'       => 'decimal',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'negotiated_total',
            'label'      => trans('b2b_suite::app.admin.quotes.index.datagrid.negotiated_total'),
            'type'       => 'decimal',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'items',
            'label'      => trans('b2b_suite::app.admin.purchase-orders.index.datagrid.items'),
            'type'       => 'string',
            'exportable' => false,
            'closure'    => function ($value) {
                $quote = app(CustomerQuoteRepository::class)->with('items')->find($value->quote_id);

                return view('b2b_suite::admin.quotes.items', compact('quote'))->render();
            },
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('b2b_suite::app.admin.quotes.index.datagrid.status'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.admin.quotes.index.datagrid.ordered'),
                    'value' => CustomerQuote::STATUS_ORDERED,
                ],
                [
                    'label' => trans('b2b_suite::app.admin.quotes.index.datagrid.completed'),
                    'value' => CustomerQuote::STATUS_COMPLETED,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                $html = '';

                if ($row->soft_deleted) {
                    $html = '<p class="label-canceled">'.trans('b2b_suite::app.admin.quotes.index.datagrid.deleted').'</p>';
                }

                switch ($row->status) {
                    case CustomerQuote::STATUS_ORDERED:
                        return '<div class="flex flex-row gap-1"><p class="label-active">'.trans('b2b_suite::app.admin.quotes.index.datagrid.ordered').'</p>'.$html.'</div>';

                    case CustomerQuote::STATUS_COMPLETED:
                        return '<div class="flex flex-row gap-1"><p class="label-canceled">'.trans('b2b_suite::app.admin.quotes.index.datagrid.completed').'</p>'.$html.'</div>';
                }
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('b2b_suite::app.admin.quotes.index.datagrid.created-at'),
            'type'            => 'datetime',
            'filterable'      => true,
            'filterable_type' => 'datetime_range',
            'sortable'        => true,
        ]);

        $this->addColumn([
            'index'           => 'expiration_date',
            'label'           => trans('b2b_suite::app.admin.quotes.index.datagrid.expiration-date'),
            'type'            => 'date',
            'filterable'      => true,
            'filterable_type' => 'date_range',
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
        if (bouncer()->hasPermission('customers.purchase_orders.view')) {
            $this->addAction([
                'index'  => 'view',
                'icon'   => 'icon-view',
                'title'  => trans('b2b_suite::app.admin.purchase-orders.index.datagrid.view'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.customers.purchase_orders.view', $row->quote_id);
                },
            ]);
        }
    }
}
