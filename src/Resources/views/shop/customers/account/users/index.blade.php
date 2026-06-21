<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.users.index.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="users" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Back Button -->
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.profile.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    @lang('b2b::app.shop.customers.account.users.index.title')
                </h2>
            </div>

            <div class="flex items-center gap-2.5 max-sm:gap-1.5">
                <!-- Add an existing platform customer as a company user. -->
                <a
                    href="{{ route('shop.customers.account.users.add_existing') }}"
                    class="secondary-button border-zinc-200 px-5 py-3 font-normal max-md:rounded-lg max-md:py-2 max-sm:py-1.5 max-sm:text-sm"
                >
                    @lang('b2b::app.shop.customers.account.users.index.add-existing-btn')
                </a>

                <a
                    href="{{ route('shop.customers.account.users.create') }}"
                    class="primary-button px-5 py-3 font-normal max-md:rounded-lg max-md:py-2 max-sm:py-1.5 max-sm:text-sm"
                >
                    @lang('b2b::app.shop.customers.account.users.index.add-btn')
                </a>
            </div>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.users.list.before') !!}

        <!-- Company users (responsive multi-row grid: stacks to one column on mobile). -->
        <x-shop::datagrid
            :src="route('shop.customers.account.users.index')"
            :isMultiRow="true"
        >
            <!-- Header -->
            <template #header="{ isLoading, available, applied, sort }">
                <template v-if="isLoading">
                    <x-b2b::shimmer.datagrid.dg.head />
                </template>

                <template v-else>
                    <div class="b2b-dg-head rounded-t-lg border-b border-zinc-200 bg-zinc-50 px-4 py-2.5">
                        <div
                            class="flex select-none items-center gap-2.5"
                            :class="{ 'b2b-dg-divider': index > 0 }"
                            v-for="(columnGroup, index) in [['full_name', 'email'], ['phone'], ['role', 'status', 'is_suspended'], []]"
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
            <template #body="{ isLoading, available, performAction }">
                <template v-if="isLoading">
                    <x-b2b::shimmer.datagrid.dg.body />
                </template>

                <template v-else>
                    <div
                        class="b2b-dg-grid border-b border-zinc-200 px-4 py-4 transition-all last:border-b-0 hover:bg-zinc-50"
                        v-for="record in available.records"
                    >
                        <!-- Identity -->
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-sm font-semibold uppercase text-zinc-600">
                                @{{ (record.full_name || '?').charAt(0) }}
                            </div>

                            <div class="grid min-w-0 gap-0.5">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-sm font-semibold text-zinc-800">@{{ record.full_name }}</span>

                                    <span
                                        v-if="record.customer_type === 'company'"
                                        class="rounded-full px-2 py-0.5 text-xs font-medium text-blue-600"
                                        style="background-color: #eff6ff;"
                                    >
                                        @lang('b2b::app.shop.customers.account.users.index.datagrid.owner')
                                    </span>
                                </div>

                                <span class="break-all text-xs text-zinc-500">@{{ record.email }}</span>
                            </div>
                        </div>

                        <!-- Contact -->
                        <div class="b2b-dg-divider flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-zinc-400">
                                @lang('b2b::app.shop.customers.account.users.index.datagrid.phone')
                            </span>

                            <span class="text-sm text-zinc-700">@{{ record.phone || '—' }}</span>
                        </div>

                        <!-- Role + Status -->
                        <div class="b2b-dg-divider flex flex-col items-start gap-2">
                            <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-700">
                                @{{ record.role }}
                            </span>

                            <div v-html="record.status"></div>

                            <div v-html="record.is_suspended"></div>
                        </div>

                        <!-- Actions -->
                        <div class="b2b-dg-divider flex items-start justify-end gap-1.5">
                            <template v-for="action in record.actions">
                                <span
                                    class="grid h-9 w-9 cursor-pointer place-items-center rounded-lg border border-zinc-200 text-xl text-zinc-500 transition-all hover:bg-zinc-100"
                                    :class="action.icon"
                                    :style="action.index === 'remove' ? 'color: #ef4444;' : ''"
                                    :title="action.title"
                                    @click="performAction(action)"
                                ></span>
                            </template>
                        </div>
                    </div>
                </template>
            </template>
        </x-shop::datagrid>

        {!! view_render_event('bagisto.shop.customers.account.users.list.after') !!}
    </div>

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the company-users multi-row grid: one column on mobile,
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
                    grid-template-columns: minmax(0, 1.8fr) minmax(0, 1fr) minmax(0, 1.2fr) minmax(5rem, 0.6fr);
                    column-gap: 1.5rem;
                }

                .b2b-dg-grid {
                    row-gap: 0;
                    align-items: center;
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
