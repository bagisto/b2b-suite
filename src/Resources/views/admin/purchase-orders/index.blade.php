<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.admin.purchase-orders.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b_suite::app.admin.purchase-orders.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.customers.purchase_orders.index') }}" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.customers.purchase_orders.index')" :isMultiRow="true">
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
                <div class="row grid grid-cols-4 grid-rows-1 items-center border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['po_number', 'name', 'status'], ['company_name', 'customer_name', 'created_at'], ['base_total', 'negotiated_total', 'expiration_date'], ['items']]"
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
                    class="row grid grid-cols-4 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Quote Id, Name, Status Section -->
                    <div class="">
                        <div class="flex gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @{{ "@lang('b2b_suite::app.admin.quotes.index.datagrid.id')".replace(':id', record.po_number) }}
                                </p>

                                <p class="text-base text-gray-800 dark:text-white">
                                    @{{ record.name }}
                                </p>

                                <p
                                    v-html="record.status"
                                >
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Company, Customer, Created At -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base text-gray-800 dark:text-white">
                                @{{ record.company_name }}
                            </p>

                            <p class="text-base text-gray-800 dark:text-white">
                                @{{ record.customer_name }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.created_at }}
                            </p>
                        </div>
                    </div>

                    <!-- Base Total, Negotiated Amount, Updated At -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(record.base_total) }}
                            </p>
                            
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ $admin.formatPrice(record.negotiated_total) }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.expiration_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Images Section -->
                    <div class="flex items-center justify-between gap-x-2">
                        <div
                            class="flex flex-col gap-1.5"
                            v-html="record.items"
                        >
                        </div>

                        <a :href=`{{ route('admin.customers.purchase_orders.view', '') }}/${record.quote_id}`>
                            <span class="icon-view rtl:icon-sort-left cursor-pointer p-1.5 text-2xl hover:rounded-md hover:bg-gray-200 ltr:ml-1 rtl:mr-1 dark:hover:bg-gray-800"></span>
                        </a>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>
</x-admin::layouts>
