<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class RoleDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('company_roles')
            ->select(
                'company_roles.id as role_id',
                'company_roles.name',
                'company_roles.permission_type'
            )
            ->where('company_roles.customer_id', auth()->guard('customer')->user()->id);
    }

    /**
     * Add Columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'role_id',
            'label'      => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'permission_type',
            'label'              => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.permission-type'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.custom'),
                    'value' => 'custom',
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.all'),
                    'value' => 'all',
                ],
            ],
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
        $this->addAction([
            'icon'   => 'icon-bin',
            'title'  => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.delete'),
            'method' => 'POST',
            'url'    => function ($row) {
                return route('shop.customers.account.roles.delete', $row->role_id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('b2b_suite::app.shop.customers.account.roles.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('shop.customers.account.roles.edit', $row->role_id);
            },
        ]);
    }
}
