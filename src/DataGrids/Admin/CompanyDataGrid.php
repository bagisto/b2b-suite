<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CompanyDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'customer_id';

    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        /**
         * Query Builder to fetch records from `company_flat` table
         */
        $queryBuilder = DB::table('company_flat')
            ->distinct()
            ->leftJoin('customers', 'company_flat.customer_id', '=', 'customers.id')
            ->select(
                'company_flat.customer_id',
                'company_flat.email',
                'company_flat.phone',
                'company_flat.business_name',
                'company_flat.website_url',
                'company_flat.vat_tax_id',
                'customers.status',
                'company_flat.created_at',
                'company_flat.updated_at'
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'company_flat.first_name, " ", '.$tablePrefix.'company_flat.last_name) as full_name'))
            ->where('customers.type', 'company')
            ->where('company_flat.locale', app()->getLocale());

        $this->addFilter('customer_id', 'company_flat.customer_id');
        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'company_flat.first_name, " ", '.$tablePrefix.'company_flat.last_name)'));
        $this->addFilter('email', 'company_flat.email');
        $this->addFilter('phone', 'company_flat.phone');
        $this->addFilter('business_name', 'company_flat.business_name');
        $this->addFilter('website_url', 'company_flat.website_url');
        $this->addFilter('vat_tax_id', 'company_flat.vat_tax_id');
        $this->addFilter('status', 'customers.status');

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'customer_id',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'full_name',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'business_name',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.business-name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'email',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.email'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'phone',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.phone'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'website_url',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.website-url'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'vat_tax_id',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.vat-tax-id'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.status'),
            'type' => 'boolean',
            'filterable' => true,
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.admin.companies.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('b2b_suite::app.admin.companies.index.datagrid.pending'),
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<span class="label-active">'.trans('b2b_suite::app.admin.companies.index.datagrid.active').'</span>';
                }

                return '<span class="label-pending">'.trans('b2b_suite::app.admin.companies.index.datagrid.pending').'</span>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('b2b_suite::app.admin.companies.index.datagrid.created-at'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
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
        if (bouncer()->hasPermission('b2b.companies.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('b2b_suite::app.admin.companies.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.b2b.companies.edit', $row->customer_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('b2b.companies.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('b2b_suite::app.admin.companies.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.b2b.companies.delete', $row->customer_id);
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
        if (bouncer()->hasPermission('b2b.companies.edit')) {
            $this->addMassAction([
                'title' => trans('b2b_suite::app.admin.companies.index.datagrid.update-status'),
                'method' => 'POST',
                'url' => route('admin.b2b.companies.mass_update_status'),
                'options' => [
                    [
                        'label' => trans('b2b_suite::app.admin.companies.index.datagrid.approve'),
                        'value' => 1,
                    ],
                    [
                        'label' => trans('b2b_suite::app.admin.companies.index.datagrid.disable'),
                        'value' => 0,
                    ],
                ],
            ]);
        }

        if (bouncer()->hasPermission('b2b.companies.delete')) {
            $this->addMassAction([
                'icon' => 'icon-delete',
                'title' => trans('b2b_suite::app.admin.companies.index.datagrid.mass-delete'),
                'method' => 'POST',
                'url' => route('admin.b2b.companies.mass_delete'),
            ]);
        }
    }
}
