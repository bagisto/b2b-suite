<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.credit.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.companies.credit.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.b2b.companies.credits.index') }}" />
        </div>
    </div>

    <x-admin::datagrid :src="route('admin.b2b.companies.credits.index')" :isMultiRow="true">
        <!-- Header -->
        <template #header="{ isLoading, available, applied, sort }">
            <template v-if="isLoading">
                <x-b2b::shimmer.datagrid.dg.head />
            </template>

            <template v-else>
                <div class="b2b-dg-head border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        :class="{ 'b2b-dg-divider': index > 0 }"
                        v-for="(columnGroup, index) in [['company_name', 'company_email', 'status'], ['credit_limit'], ['outstanding_balance', 'available_credit'], []]"
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
        <template #body="{ isLoading, available }">
            <template v-if="isLoading">
                <x-b2b::shimmer.datagrid.dg.body />
            </template>

            <template v-else>
                <div
                    class="b2b-dg-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Company -->
                    <div class="flex flex-col gap-2">
                        <a
                            class="w-fit text-base font-semibold text-gray-800 transition-all hover:text-blue-600 dark:text-white"
                            :href=`{{ route('admin.b2b.companies.index') }}/${record.company_id}/credit`
                        >
                            @{{ record.company_name }}
                        </a>

                        <p
                            class="text-sm text-gray-600 dark:text-gray-300"
                            v-if="record.company_email"
                        >
                            @{{ record.company_email }}
                        </p>

                        <div v-html="record.status"></div>
                    </div>

                    <!-- Credit limit -->
                    <div class="b2b-dg-divider flex flex-col gap-0.5">
                        <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                            @lang('b2b::app.admin.companies.credit.credit-limit')
                        </span>
                        <p class="text-base font-medium text-gray-800 dark:text-white">@{{ $admin.formatPrice(record.credit_limit) }}</p>
                    </div>

                    <!-- Outstanding / Available -->
                    <div class="b2b-dg-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.credit.outstanding-balance')
                            </span>
                            <p
                                class="text-sm font-medium"
                                :class="parseFloat(record.outstanding_balance) > 0 ? 'text-red-600' : 'text-gray-700 dark:text-gray-200'"
                            >@{{ $admin.formatPrice(Math.max(0, parseFloat(record.outstanding_balance))) }}</p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.companies.credit.available-credit')
                            </span>
                            <p
                                class="text-sm font-bold"
                                :class="parseFloat(record.available_credit) > 0 ? 'text-green-600' : 'text-gray-800 dark:text-white'"
                            >@{{ $admin.formatPrice(record.available_credit) }}</p>
                        </div>
                    </div>

                    <!-- Action -->
                    <div class="b2b-dg-divider flex items-start justify-end">
                        <a
                            class="secondary-button"
                            :href=`{{ route('admin.b2b.companies.index') }}/${record.company_id}/credit`
                        >
                            @lang('b2b::app.admin.companies.credit.manage')
                        </a>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the B2B multi-row datagrid (one column on mobile, four
             * aligned columns on desktop). The grid variants / dividers used here are purged
             * out of the B2B admin bundle, so they live in this scoped block.
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
                    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr) minmax(0, 1.2fr) minmax(10.5rem, 1fr);
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
