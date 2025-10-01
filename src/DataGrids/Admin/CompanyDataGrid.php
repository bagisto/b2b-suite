<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

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
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        /**
         * Query Builder to fetch records from `customer_flat` table
         */
        $queryBuilder = DB::table('customer_flat')
            ->distinct()
            ->leftJoin('customers', 'customer_flat.customer_id', '=', 'customers.id')
            ->select(
                'customer_flat.customer_id',
                'customer_flat.email',
                'customer_flat.phone',
                'customer_flat.business_name',
                'customer_flat.website_url',
                'customer_flat.vat_tax_id',
                'customers.status',
                'customer_flat.created_at',
                'customer_flat.updated_at'
            )
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'customer_flat.first_name, " ", '.$tablePrefix.'customer_flat.last_name) as full_name'))
            ->where('customers.type', 'company')
            ->where('customer_flat.locale', app()->getLocale());

        $this->addFilter('customer_id', 'customer_flat.customer_id');
        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'customer_flat.first_name, " ", '.$tablePrefix.'customer_flat.last_name)'));
        $this->addFilter('email', 'customer_flat.email');
        $this->addFilter('phone', 'customer_flat.phone');
        $this->addFilter('business_name', 'customer_flat.business_name');
        $this->addFilter('website_url', 'customer_flat.website_url');
        $this->addFilter('vat_tax_id', 'customer_flat.vat_tax_id');
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
            'index'      => 'customer_id',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'business_name',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.business-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.phone'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'website_url',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.website-url'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'vat_tax_id',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.vat-tax-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('b2b_suite::app.admin.companies.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.admin.companies.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('b2b_suite::app.admin.companies.index.datagrid.disable'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('b2b_suite::app.admin.companies.index.datagrid.created-at'),
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('customer.companies.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('b2b_suite::app.admin.companies.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.customers.companies.edit', $row->customer_id);
                },
            ]);
        }

        if (bouncer()->hasPermission('customer.companies.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('b2b_suite::app.admin.companies.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.customers.companies.delete', $row->customer_id);
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
        if (bouncer()->hasPermission('customer.companies.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('b2b_suite::app.admin.companies.index.datagrid.mass-delete'),
                'method' => 'POST',
                'url'    => route('admin.customers.companies.mass_delete'),
            ]);
        }
    }
}
