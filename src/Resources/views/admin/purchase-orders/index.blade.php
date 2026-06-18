<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.admin.purchase-orders.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.purchase-orders.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.b2b.purchase_orders.index') }}" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.b2b.purchase_orders.index')" :isMultiRow="true">
        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="b2b-dg-head border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        :class="{ 'b2b-dg-divider': index > 0 }"
                        v-for="(columnGroup, index) in [['po_number', 'name', 'status'], ['company_name', 'agent_name', 'customer_name', 'created_at'], ['base_total', 'negotiated_total', 'expiration_date'], ['items']]"
                    >
                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 ltr:ml-1.5 rtl:mr-1.5 dark:text-white"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            >
                            </i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    class="b2b-dg-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- PO Id, Name, Status -->
                    <div class="flex flex-col gap-2">
                        <a
                            class="w-fit text-base font-semibold text-gray-800 transition-all hover:text-blue-600 dark:text-white"
                            :href=`{{ route('admin.b2b.purchase_orders.index') }}/${record.quote_id}`
                        >
                            @{{ "@lang('b2b::app.admin.quotes.index.datagrid.id')".replace(':id', record.po_number) }}
                        </a>

                        <p
                            class="text-sm text-gray-600 dark:text-gray-300"
                            v-if="record.name"
                        >
                            @{{ record.name }}
                        </p>

                        <div v-html="record.status"></div>
                    </div>

                    <!-- Company, Customer, Created At -->
                    <div class="b2b-dg-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.company')
                            </span>

                            <p class="break-words text-sm font-medium text-gray-800 dark:text-white">
                                @{{ record.company_name }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.sales-representative')
                            </span>

                            <p class="break-words text-sm text-gray-700 dark:text-gray-300">
                                @{{ record.agent_name || '—' }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.customer')
                            </span>

                            <p class="break-words text-sm text-gray-700 dark:text-gray-300">
                                @{{ record.customer_name }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.created-at')
                            </span>

                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @{{ record.created_at }}
                            </p>
                        </div>
                    </div>

                    <!-- Base Total, Negotiated Total, Expiration -->
                    <div class="b2b-dg-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.base_total')
                            </span>

                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @{{ $admin.formatPrice(record.base_total) }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.negotiated_total')
                            </span>

                            <p class="text-base font-bold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(record.negotiated_total) }}
                            </p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.quotes.index.datagrid.expiration-date')
                            </span>

                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                @{{ record.expiration_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Items + View action -->
                    <div class="b2b-dg-divider flex items-start justify-between gap-2">
                        <div
                            class="min-w-0"
                            v-html="record.items"
                        >
                        </div>

                        <a
                            class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            :href=`{{ route('admin.b2b.purchase_orders.index') }}/${record.quote_id}`
                            title="@lang('b2b::app.admin.quotes.index.datagrid.view')"
                        >
                            <span class="icon-view text-2xl"></span>
                        </a>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the B2B multi-row datagrid. The grid variants /
             * dividers used here are purged out of the B2B admin bundle, so they live in
             * this scoped block: one column on mobile, four aligned columns on desktop.
             */
            .b2b-dg-grid,
            .b2b-dg-head {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
            }

            .b2b-dg-grid {
                gap: 1.25rem;
            }

            .b2b-dg-head {
                display: none;
            }

            @media (min-width: 1024px) {
                .b2b-dg-grid,
                .b2b-dg-head {
                    grid-template-columns: 1.5fr 1.1fr 1.1fr 1.3fr;
                    column-gap: 1.5rem;
                }

                .b2b-dg-grid {
                    row-gap: 0;
                    align-items: start;
                }

                .b2b-dg-head {
                    display: grid;
                    align-items: center;
                }

                .b2b-dg-divider {
                    border-inline-start: 1px solid rgb(243 244 246);
                    padding-inline-start: 1.5rem;
                }

                .dark .b2b-dg-divider {
                    border-inline-start-color: rgb(31 41 55);
                }
            }
        </style>
    @endPushOnce
</x-admin::layouts>
