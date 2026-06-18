<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.companies.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.b2b.companies.index') }}" />

            @if (bouncer()->hasPermission('b2b.companies.create'))
                <a href="{{ route('admin.b2b.companies.create') }}">
                    <div class="primary-button">
                        @lang('b2b::app.admin.companies.index.create-btn')
                    </div>
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.b2b.companies.list.before') !!}

    <x-admin::datagrid :src="route('admin.b2b.companies.index')" :isMultiRow="true">
        <!-- Header -->
        <template #header="{ isLoading, available, applied, selectAll, sort }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="b2b-company-head border-b px-4 py-2.5 dark:border-gray-800">
                    <!-- Select all -->
                    <p v-if="available.massActions.length">
                        <label for="mass_action_select_all_records">
                            <input
                                type="checkbox"
                                id="mass_action_select_all_records"
                                class="peer hidden"
                                :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                @change="selectAll"
                            >

                            <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"></span>
                        </label>
                    </p>
                    <p v-else></p>

                    <div
                        class="flex select-none items-center gap-2.5"
                        :class="{ 'b2b-company-divider': index > 0 }"
                        v-for="(columnGroup, index) in [['business_name', 'full_name', 'status'], ['email', 'phone'], ['vat_tax_id', 'sales_rep_name', 'created_at'], []]"
                    >
                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(c => c.index === column)?.sortable,
                                        }"
                                        @click="available.columns.find(c => c.index === column)?.sortable ? sort(available.columns.find(c => c.index === column)) : {}"
                                    >
                                        @{{ available.columns.find(c => c.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 ltr:ml-1.5 rtl:mr-1.5 dark:text-white"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat' : 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            >
                            </i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <!-- Body -->
        <template #body="{ isLoading, available, applied, performAction }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="b2b-company-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Select -->
                    <p v-if="available.massActions.length">
                        <label :for="`mass_action_select_record_${record.customer_id}`">
                            <input
                                type="checkbox"
                                :id="`mass_action_select_record_${record.customer_id}`"
                                :value="record.customer_id"
                                class="peer hidden"
                                v-model="applied.massActions.indices"
                            >

                            <span class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"></span>
                        </label>
                    </p>
                    <p v-else></p>

                    <!-- Identity -->
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @{{ record.business_name || record.full_name }}
                        </p>

                        <p
                            class="text-sm text-gray-600 dark:text-gray-300"
                            v-if="record.business_name && record.full_name"
                        >
                            @{{ record.full_name }}
                        </p>

                        <div v-html="record.status"></div>
                    </div>

                    <!-- Contact -->
                    <div class="b2b-company-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.index.datagrid.email')
                            </span>
                            <p class="break-words text-sm text-gray-700 dark:text-gray-200">@{{ record.email || '—' }}</p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.index.datagrid.phone')
                            </span>
                            <p class="text-sm text-gray-700 dark:text-gray-200">@{{ record.phone || '—' }}</p>
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="b2b-company-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.index.datagrid.vat-tax-id')
                            </span>
                            <p class="break-words text-sm text-gray-700 dark:text-gray-200">@{{ record.vat_tax_id || '—' }}</p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.index.datagrid.sales-representative')
                            </span>
                            <p class="break-words text-sm text-gray-700 dark:text-gray-200">@{{ record.sales_rep_name || '—' }}</p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.index.datagrid.created-at')
                            </span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">@{{ record.created_at }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="b2b-company-divider flex items-start justify-end gap-1.5">
                        <template v-for="action in record.actions">
                            <span
                                class="grid h-9 w-9 cursor-pointer place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                :class="action.icon"
                                :title="action.title"
                                @click="performAction(action)"
                            ></span>
                        </template>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.b2b.companies.list.after') !!}

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the companies multi-row datagrid: one column on mobile,
             * a select column + four aligned columns on desktop. These grid utilities are
             * purged out of the B2B admin bundle, so they live in this scoped block.
             */
            .b2b-company-grid,
            .b2b-company-head {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
            }

            .b2b-company-grid {
                gap: 1rem;
            }

            .b2b-company-head {
                display: none;
            }

            @media (min-width: 1024px) {
                .b2b-company-grid,
                .b2b-company-head {
                    grid-template-columns: 2.5rem minmax(0, 1.6fr) minmax(0, 1.2fr) minmax(0, 1.2fr) minmax(9rem, 0.9fr);
                    column-gap: 1.25rem;
                }

                .b2b-company-grid {
                    row-gap: 0;
                    align-items: start;
                }

                .b2b-company-head {
                    display: grid;
                    align-items: center;
                }

                .b2b-company-divider {
                    border-inline-start: 1px solid rgb(243 244 246);
                    padding-inline-start: 1.25rem;
                }

                .dark .b2b-company-divider {
                    border-inline-start-color: rgb(31 41 55);
                }
            }
        </style>
    @endPushOnce
</x-admin::layouts>
