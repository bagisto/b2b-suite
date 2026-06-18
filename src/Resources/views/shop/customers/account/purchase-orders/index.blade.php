<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.purchase-orders.index.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="purchase-orders" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-sm:mb-5">
            <!-- Back Button -->
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.profile.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('b2b::app.shop.customers.account.purchase-orders.index.title')
            </h2>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.purchase_orders.list.before') !!}

        <x-shop::datagrid :src="route('shop.customers.account.purchase_orders.index')" :isMultiRow="true">
            <!-- Header -->
            <template #header="{ isLoading, available, applied, sort }">
                <template v-if="isLoading">
                    <x-shop::shimmer.datagrid.table.head />
                </template>

                <template v-else>
                    <div class="b2b-dg-head rounded-t-lg border-b border-zinc-200 bg-zinc-50 px-4 py-2.5">
                        <div
                            class="flex select-none items-center gap-2.5"
                            :class="{ 'b2b-dg-divider': index > 0 }"
                            v-for="(columnGroup, index) in [['po_number', 'name', 'status'], ['customer_name', 'created_at'], ['base_total', 'negotiated_total'], []]"
                        >
                            <p class="text-zinc-600">
                                <span class="[&>*]:after:content-['_/_']">
                                    <template v-for="column in columnGroup">
                                        <span
                                            class="after:content-['/'] last:after:content-['']"
                                            :class="{
                                                'font-medium text-zinc-800': applied.sort.column == column,
                                                'cursor-pointer hover:text-zinc-800': available.columns.find(c => c.index === column)?.sortable,
                                            }"
                                            @click="available.columns.find(c => c.index === column)?.sortable ? sort(available.columns.find(c => c.index === column)) : {}"
                                        >
                                            @{{ available.columns.find(c => c.index === column)?.label }}
                                        </span>
                                    </template>
                                </span>

                                <i
                                    class="align-text-bottom text-base text-zinc-800 ltr:ml-1.5 rtl:mr-1.5"
                                    :class="[applied.sort.order === 'asc' ? 'icon-arrow-down' : 'icon-arrow-up']"
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
                    <x-shop::shimmer.datagrid.table.body />
                </template>

                <template v-else>
                    <div
                        class="b2b-dg-grid border-b border-zinc-200 px-4 py-4 transition-all last:border-b-0 hover:bg-zinc-50"
                        v-for="record in available.records"
                    >
                        <!-- PO number, name, status -->
                        <div class="flex flex-col gap-2">
                            <a
                                class="w-fit text-base font-semibold text-zinc-800 transition-all hover:text-blue-600"
                                :href="record.actions[0].url"
                            >
                                #@{{ record.po_number }}
                            </a>

                            <p class="text-sm text-zinc-600" v-if="record.name">@{{ record.name }}</p>

                            <div v-html="record.status"></div>
                        </div>

                        <!-- Customer, Created At -->
                        <div class="b2b-dg-divider flex flex-col gap-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.quotes.index.datagrid.customer')</span>
                                <p class="break-words text-sm font-medium text-zinc-800">@{{ record.customer_name || '—' }}</p>
                            </div>

                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.quotes.index.datagrid.created-at')</span>
                                <p class="text-sm text-zinc-600">@{{ record.created_at }}</p>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="b2b-dg-divider flex flex-col gap-3">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.quotes.index.datagrid.base_total')</span>
                                <p class="text-sm font-medium text-zinc-700">@{{ record.base_total }}</p>
                            </div>

                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.quotes.index.datagrid.negotiated_total')</span>
                                <p class="text-base font-bold text-zinc-800">@{{ record.negotiated_total }}</p>
                            </div>
                        </div>

                        <!-- View action -->
                        <div class="b2b-dg-divider flex items-start justify-end">
                            <a
                                class="grid h-9 w-9 shrink-0 place-items-center rounded-md border border-zinc-200 text-zinc-600 transition-all hover:bg-zinc-100"
                                :href="record.actions[0].url"
                                title="@lang('b2b::app.shop.customers.account.quotes.index.datagrid.view')"
                            >
                                <span class="icon-eye text-2xl"></span>
                            </a>
                        </div>
                    </div>
                </template>
            </template>
        </x-shop::datagrid>

        {!! view_render_event('bagisto.shop.customers.account.purchase_orders.list.after') !!}
    </div>

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the B2B multi-row datagrid (shop): one column on mobile,
             * four aligned columns on desktop. These grid utilities / dividers are purged out
             * of the B2B shop bundle, so they live in this scoped block.
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
                    grid-template-columns: minmax(0, 1.6fr) minmax(0, 1.3fr) minmax(0, 1.1fr) minmax(4rem, 0.5fr);
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
                    border-inline-start: 1px solid rgb(228 228 231);
                    padding-inline-start: 1.5rem;
                }
            }
        </style>
    @endPushOnce
</x-shop::layouts.account>
