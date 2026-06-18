<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\Customer;
use Webkul\DataGrid\DataGrid;

class CompanyCatalogDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();

        $queryBuilder = DB::table('company_catalogs')
            ->select(
                'company_catalogs.id',
                'company_catalogs.name',
                'company_catalogs.description',
                'company_catalogs.status',
                'company_catalogs.status as status_value',
                'company_catalogs.created_by',
                'company_catalogs.created_at',
                DB::raw('(SELECT COUNT(*) FROM '.$tablePrefix.'company_catalog_products WHERE '.$tablePrefix.'company_catalog_products.company_catalog_id = '.$tablePrefix.'company_catalogs.id) as products_count'),
                DB::raw('(SELECT COUNT(*) FROM '.$tablePrefix.'customers WHERE '.$tablePrefix.'customers.company_catalog_id = '.$tablePrefix.'company_catalogs.id) as companies_count')
            );

        $this->addFilter('id', 'company_catalogs.id');
        $this->addFilter('name', 'company_catalogs.name');
        $this->addFilter('status', 'company_catalogs.status');
        $this->addFilter('created_at', 'company_catalogs.created_at');

        /**
         * A sales rep only sees catalogs assigned to a company they manage; super-admins
         * see every catalog.
         */
        if ($repId = Customer::salesRepScopeId()) {
            $queryBuilder->whereIn('company_catalogs.id', function ($query) use ($repId) {
                $query->select('company_catalog_id')
                    ->from('customers')
                    ->where('sales_rep_id', $repId)
                    ->whereNotNull('company_catalog_id');
            });
        }

        return $queryBuilder;
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'id',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.id'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'name',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.name'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'products_count',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.products'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'companies_count',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.companies'),
            'type' => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable' => false,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.status'),
            'type' => 'boolean',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable' => true,
            'closure' => function ($row) {
                if ($row->status) {
                    return '<p class="label-active">'.trans('b2b::app.admin.company-catalogs.index.datagrid.active').'</p>';
                }

                return '<p class="label-info">'.trans('b2b::app.admin.company-catalogs.index.datagrid.inactive').'</p>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('b2b::app.admin.company-catalogs.index.datagrid.created-at'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'datetime_range',
            'sortable' => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('b2b.company-catalogs.edit')) {
            $this->addAction([
                'index' => 'edit',
                'icon' => 'icon-edit',
                'title' => trans('b2b::app.admin.company-catalogs.index.datagrid.edit'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.b2b.company_catalogs.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('b2b.company-catalogs.delete')) {
            $this->addAction([
                'index' => 'delete',
                'icon' => 'icon-delete',
                'title' => trans('b2b::app.admin.company-catalogs.index.datagrid.delete'),
                'method' => 'DELETE',
                'url' => function ($row) {
                    return route('admin.b2b.company_catalogs.delete', $row->id);
                },
            ]);
        }
    }
}
