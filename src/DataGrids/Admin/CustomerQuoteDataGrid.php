<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\Customer;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
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

        $queryBuilder = DB::table('customer_quotes')
            ->distinct()
            ->leftJoin('customers as company', 'customer_quotes.company_id', '=', 'company.id')
            ->leftJoin('customers as customer', 'customer_quotes.customer_id', '=', 'customer.id')
            ->leftJoin('admins as agent', 'company.sales_rep_id', '=', 'agent.id')
            ->leftJoin('company_flat', function ($join) {
                $join->on('customer_quotes.company_id', '=', 'company_flat.customer_id')
                    ->where('company_flat.locale', '=', app()->getLocale());
            })
            ->addSelect(
                'customer_quotes.id as quote_id',
                'customer_quotes.quotation_number',
                'customer_quotes.name',
                'agent.name as agent_name',
                'customer_quotes.base_total',
                'customer_quotes.negotiated_total',
                'customer_quotes.status',
                'customer_quotes.order_id',
                'customer_quotes.soft_deleted',
                'customer_quotes.created_at',
                'customer_quotes.expiration_date'
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'customer.first_name, " ", '.$tablePrefix.'customer.last_name) as customer_name'))
            ->addSelect(DB::raw('COALESCE(NULLIF('.$tablePrefix.'company_flat.business_name, ""), CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name)) as company_name'))
            ->whereIn('customer_quotes.state', [
                CustomerQuote::STATE_QUOTATION,
                CustomerQuote::STATE_PURCHASE_ORDER,
            ])
            ->whereIn('customer_quotes.status', [
                CustomerQuote::STATUS_OPEN,
                CustomerQuote::STATUS_NEGOTIATION,
                CustomerQuote::STATUS_ACCEPTED,
                CustomerQuote::STATUS_REJECTED,
                CustomerQuote::STATUS_ORDERED,
                CustomerQuote::STATUS_COMPLETED,
            ])
            ->where(function ($query) {
                $query->where('customer_quotes.status', '!=', CustomerQuote::STATUS_DRAFT)
                    ->orWhereNotNull('customer_quotes.soft_deleted');
            });

        $this->addFilter('quotation_number', 'customer_quotes.quotation_number');
        $this->addFilter('name', 'customer_quotes.name');
        $this->addFilter('status', 'customer_quotes.status');
        $this->addFilter('base_total', 'customer_quotes.base_total');
        $this->addFilter('negotiated_total', 'customer_quotes.negotiated_total');
        $this->addFilter('customer_name', DB::raw('CONCAT('.$tablePrefix.'customer.first_name, " ", '.$tablePrefix.'customer.last_name)'));
        $this->addFilter('company_name', DB::raw('COALESCE(NULLIF('.$tablePrefix.'company_flat.business_name, ""), CONCAT('.$tablePrefix.'company.first_name, " ", '.$tablePrefix.'company.last_name))'));
        $this->addFilter('agent_name', 'agent.name');
        $this->addFilter('created_at', 'customer_quotes.created_at');
        $this->addFilter('expiration_date', 'customer_quotes.expiration_date');

        // A sales rep only sees quotes for the companies they manage; super-admins see all.
        if ($repId = Customer::salesRepScopeId()) {
            $queryBuilder->where('company.sales_rep_id', $repId);
        }

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'quotation_number',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.quote-id'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'company_name',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.company'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_name',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.customer'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'agent_name',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.sales-representative'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'base_total',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.base_total'),
            'type' => 'decimal',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'negotiated_total',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.negotiated_total'),
            'type' => 'decimal',
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'items',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.items'),
            'type' => 'string',
            'exportable' => false,
            'closure' => function ($value) {
                $quote = app(CustomerQuoteRepository::class)->with('items')->find($value->quote_id);

                return view('b2b::admin.quotes.items', compact('quote'))->render();
            },
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.status'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.open'),
                    'value' => CustomerQuote::STATUS_OPEN,
                ],
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.accepted'),
                    'value' => CustomerQuote::STATUS_ACCEPTED,
                ],
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.negotiation'),
                    'value' => CustomerQuote::STATUS_NEGOTIATION,
                ],
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.rejected'),
                    'value' => CustomerQuote::STATUS_REJECTED,
                ],
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.ordered'),
                    'value' => CustomerQuote::STATUS_ORDERED,
                ],
                [
                    'label' => trans('b2b::app.admin.quotes.index.datagrid.completed'),
                    'value' => CustomerQuote::STATUS_COMPLETED,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                $deleted = $row->soft_deleted
                    ? '<p class="label-canceled">'.trans('b2b::app.admin.quotes.index.datagrid.deleted').'</p>'
                    : '';

                $label = trans('b2b::app.admin.quotes.index.datagrid.'.$row->status);

                return '<div class="flex flex-row gap-1"><p class="'.CustomerQuote::statusLabelClass($row->status).'">'.$label.'</p>'.$deleted.'</div>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.created-at'),
            'type' => 'datetime',
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'expiration_date',
            'label' => trans('b2b::app.admin.quotes.index.datagrid.expiration-date'),
            'type' => 'date',
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('b2b.quotes.view')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-view',
                'title' => trans('b2b::app.admin.quotes.index.datagrid.view'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.b2b.quotes.view', $row->quote_id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        if (bouncer()->hasPermission('b2b.quotes.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('b2b::app.admin.quotes.index.datagrid.mass-delete'),
                'method' => 'POST',
                'url' => route('admin.b2b.quotes.mass_delete'),
            ]);
        }

        if (bouncer()->hasPermission('b2b.quotes.edit')) {
            $this->addMassAction([
                'icon' => 'icon-edit',
                'title' => trans('b2b::app.admin.quotes.index.datagrid.mass-update'),
                'method' => 'POST',
                'url' => route('admin.b2b.quotes.mass_update'),
                'options' => [
                    [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.draft'),
                        'value' => 'draft',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.open'),
                        'value' => 'open',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.accepted'),
                        'value' => 'accepted',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.negotiation'),
                        'value' => 'negotiation',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.ordered'),
                        'value' => 'ordered',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.cancelled'),
                        'value' => 'cancelled',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.rejected'),
                        'value' => 'rejected',
                    ], [
                        'label' => trans('b2b::app.admin.quotes.index.datagrid.closed'),
                        'value' => 'closed',
                    ],
                ],
            ]);
        }
    }
}
