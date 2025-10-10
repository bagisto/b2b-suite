<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Support\Facades\DB;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\DataGrid\DataGrid;

class UserDataGrid extends DataGrid
{
    /**
     * Index.
     *
     * @var string
     */
    protected $primaryColumn = 'user_id';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CustomerGroupRepository $customerGroupRepository) {}

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        $tablePrefix = DB::getTablePrefix();
        $customer = auth()->guard('customer')->user();

        $companyId = $customer->type === 'company'
            ? $customer->id
            : DB::table('customer_companies')
                ->where('customer_id', $customer->id)
                ->value('company_id');

        $queryBuilder = DB::table('customers')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->addSelect(
                'customers.id as user_id',
                'customers.email',
                'customers.phone',
                'customers.gender',
                'customers.status',
                'customers.is_suspended',
                'customer_groups.name as group'
            )
            ->addSelect(DB::raw('CONCAT(' . $tablePrefix . 'customers.first_name, " ", ' . $tablePrefix . 'customers.last_name) as full_name'))
            ->where('customers.type', 'user')
            ->where('customers.id', '!=', $customer->id)
            ->groupBy('customers.id');

        $queryBuilder = $queryBuilder->join('customer_companies', function ($join) use ($companyId) {
            $join->on('customers.id', '=', 'customer_companies.customer_id')
                ->where('customer_companies.company_id', $companyId);
        });

        $this->addFilter('user_id', 'customers.id');
        $this->addFilter('email', 'customers.email');
        $this->addFilter('full_name', DB::raw('CONCAT(' . $tablePrefix . 'customers.first_name, " ", ' . $tablePrefix . 'customers.last_name)'));
        $this->addFilter('group', 'customer_groups.name');
        $this->addFilter('phone', 'customers.phone');
        $this->addFilter('status', 'customers.status');

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'user_id',
            'label'      => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.phone'),
            'type'       => 'integer',
            'filterable' => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.status'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->status) {
                    return '<p class="label-active">'.trans('b2b_suite::app.shop.customers.account.users.index.datagrid.active').'</p>';
                }

                return '<p class="label-canceled">'.trans('b2b_suite::app.shop.customers.account.users.index.datagrid.inactive').'</p>';
            },
        ]);

        $this->addColumn([
            'index'      => 'gender',
            'label'      => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.gender'),
            'type'       => 'string',
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'group',
            'label'              => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.group'),
            'type'               => 'string',
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => $this->customerGroupRepository->all(['name as label', 'name as value'])->toArray(),
        ]);

        $this->addColumn([
            'index'              => 'is_suspended',
            'label'              => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.is-suspended'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.suspended'),
                    'value' => 1,
                ],
                [
                    'label' => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.not-suspended'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->is_suspended) {
                    return '<p class="label-canceled">'.trans('b2b_suite::app.shop.customers.account.users.index.datagrid.suspended').'</p>';
                }

                return '-';
            },
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
            'title'  => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.delete'),
            'method' => 'POST',
            'url'    => function ($row) {
                return route('shop.customers.account.users.delete', $row->user_id);
            },
        ]);

        $this->addAction([
            'icon'   => 'icon-edit',
            'title'  => trans('b2b_suite::app.shop.customers.account.users.index.datagrid.edit'),
            'method' => 'GET',
            'url'    => function ($row) {
                return route('shop.customers.account.users.edit', $row->user_id);
            },
        ]);
    }
}
