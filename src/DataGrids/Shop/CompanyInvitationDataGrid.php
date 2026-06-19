<?php

namespace Webkul\B2BSuite\DataGrids\Shop;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\B2BSuite\Models\CompanyInvitation;
use Webkul\DataGrid\DataGrid;

class CompanyInvitationDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'invitation_id';

    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $customer = auth()->guard('customer')->user();

        $companyId = $customer->type === 'company'
            ? $customer->id
            : DB::table('customer_companies')
                ->where('customer_id', $customer->id)
                ->value('company_id');

        $queryBuilder = DB::table('company_invitations')
            ->leftJoin('company_roles', 'company_invitations.company_role_id', '=', 'company_roles.id')
            ->addSelect(
                'company_invitations.id as invitation_id',
                'company_invitations.email',
                'company_roles.name as role',
                'company_invitations.expires_at',
                'company_invitations.created_at'
            )
            ->where('company_invitations.company_id', $companyId)
            ->where('company_invitations.status', CompanyInvitation::STATUS_PENDING);

        $this->addFilter('invitation_id', 'company_invitations.id');
        $this->addFilter('email', 'company_invitations.email');
        $this->addFilter('role', 'company_roles.name');
        $this->addFilter('created_at', 'company_invitations.created_at');

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
            'index' => 'email',
            'label' => trans('b2b::app.shop.customers.account.users.existing.datagrid.email'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'role',
            'label' => trans('b2b::app.shop.customers.account.users.existing.datagrid.role'),
            'type' => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable' => true,
            'closure' => function ($row) {
                return $row->role ?: '—';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('b2b::app.shop.customers.account.users.existing.datagrid.invited-on'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable' => true,
        ]);

        $this->addColumn([
            'index' => 'expires_at',
            'label' => trans('b2b::app.shop.customers.account.users.existing.datagrid.expires'),
            'type' => 'datetime',
            'searchable' => false,
            'filterable' => false,
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
        $this->addAction([
            'icon' => 'icon-bin',
            'title' => trans('b2b::app.shop.customers.account.users.existing.btn-revoke'),
            'method' => 'POST',
            'url' => function ($row) {
                return route('shop.customers.account.users.revoke_invitation', $row->invitation_id);
            },
        ]);
    }
}
