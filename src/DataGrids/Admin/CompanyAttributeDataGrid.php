<?php

namespace Webkul\B2BSuite\DataGrids\Admin;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CompanyAttributeDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     */
    public function prepareQueryBuilder(): Builder
    {
        return DB::table('company_attributes')
            ->select(
                'id',
                'code',
                'admin_name',
                'type',
                'is_required',
                'is_unique',
                'value_per_locale',
                'value_per_channel',
                'created_at'
            );
    }

    /**
     * Prepare columns.
     */
    public function prepareColumns(): void
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'admin_name',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'type',
            'label'              => trans('b2b_suite::app.admin.company-attributes.index.datagrid.type'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.text'),
                    'value' => 'text',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.textarea'),
                    'value' => 'textarea',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.boolean'),
                    'value' => 'boolean',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.select'),
                    'value' => 'select',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.multiselect'),
                    'value' => 'multiselect',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.date-time'),
                    'value' => 'datetime',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.date'),
                    'value' => 'date',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.image'),
                    'value' => 'image',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.file'),
                    'value' => 'file',
                ],
                [
                    'label' => trans('b2b_suite::app.admin.company-attributes.index.datagrid.checkbox'),
                    'value' => 'checkbox',
                ],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'is_required',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.required'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->is_required) {
                    return trans('b2b_suite::app.admin.company-attributes.index.datagrid.true');
                }

                return trans('b2b_suite::app.admin.company-attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'is_unique',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.unique'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->is_unique) {
                    return trans('b2b_suite::app.admin.company-attributes.index.datagrid.true');
                }

                return trans('b2b_suite::app.admin.company-attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'value_per_locale',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.locale-based'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->value_per_locale) {
                    return trans('b2b_suite::app.admin.company-attributes.index.datagrid.true');
                }

                return trans('b2b_suite::app.admin.company-attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'value_per_channel',
            'label'      => trans('b2b_suite::app.admin.company-attributes.index.datagrid.channel-based'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->value_per_channel) {
                    return trans('b2b_suite::app.admin.company-attributes.index.datagrid.true');
                }

                return trans('b2b_suite::app.admin.company-attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('b2b_suite::app.admin.company-attributes.index.datagrid.created-at'),
            'type'            => 'date',
            'searchable'      => true,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
        ]);
    }

    /**
     * Prepare actions.
     */
    public function prepareActions(): void
    {
        if (bouncer()->hasPermission('b2b_suite.company_attributes.edit')) {
            $this->addAction([
                'icon'   => 'icon-edit',
                'title'  => trans('b2b_suite::app.admin.company-attributes.index.datagrid.edit'),
                'method' => 'GET',
                'url'    => function ($row) {
                    return route('admin.customers.attributes.edit', $row->id);
                },
            ]);
        }

        if (bouncer()->hasPermission('b2b_suite.company_attributes.delete')) {
            $this->addAction([
                'icon'   => 'icon-delete',
                'title'  => trans('b2b_suite::app.admin.company-attributes.index.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => function ($row) {
                    return route('admin.customers.attributes.delete', $row->id);
                },
            ]);
        }
    }

    /**
     * Prepare mass actions.
     */
    public function prepareMassActions(): void
    {
        if (bouncer()->hasPermission('b2b_suite.company_attributes.delete')) {
            $this->addMassAction([
                'icon'   => 'icon-delete',
                'title'  => trans('b2b_suite::app.admin.company-attributes.index.datagrid.delete'),
                'method' => 'POST',
                'url'    => route('admin.customers.attributes.mass_delete'),
            ]);
        }
    }
}
