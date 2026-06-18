@php
    $available = (float) $credit->credit_limit - (float) $credit->outstanding_balance;
@endphp

<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.credit.title')
    </x-slot>

    <!-- Header -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
            @lang('b2b::app.admin.companies.credit.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <!-- Back -->
            <a
                href="{{ route('admin.b2b.companies.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('b2b::app.admin.companies.credit.back')
            </a>

            <!-- Credit settings — opens a modal -->
            <x-admin::form :action="route('admin.b2b.companies.credit.limit', $company->id)">
                <x-admin::modal>
                    <x-slot:toggle>
                        <button type="button" class="secondary-button">
                            @lang('b2b::app.admin.companies.credit.set-limit')
                        </button>
                    </x-slot>

                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.companies.credit.settings-title')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">@lang('b2b::app.admin.companies.credit.credit-limit')</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control
                                type="text"
                                name="credit_limit"
                                rules="required|decimal:0,4|min_value:0"
                                :value="(float) $credit->credit_limit"
                            />
                            <x-admin::form.control-group.error control-name="credit_limit" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="flex items-center gap-2">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" name="status" value="1" @checked($credit->status)>
                            <x-admin::form.control-group.label class="!mb-0">@lang('b2b::app.admin.companies.credit.enabled')</x-admin::form.control-group.label>
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>@lang('b2b::app.admin.companies.credit.comment')</x-admin::form.control-group.label>
                            <x-admin::form.control-group.control type="textarea" name="comment" rows="2" />
                        </x-admin::form.control-group>
                    </x-slot>

                    <x-slot:footer>
                        <button type="submit" class="primary-button">@lang('b2b::app.admin.companies.credit.save')</button>
                    </x-slot>
                </x-admin::modal>
            </x-admin::form>

            <!-- Reimburse (offline payment) — opens a modal; available even when the
                 balance is settled or in advance, so further payments can be recorded -->
            <x-admin::form :action="route('admin.b2b.companies.credit.reimburse', $company->id)">
                    <x-admin::modal>
                        <x-slot:toggle>
                            <button type="button" class="primary-button">
                                @lang('b2b::app.admin.companies.credit.reimburse')
                            </button>
                        </x-slot>

                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('b2b::app.admin.companies.credit.reimburse')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">@lang('b2b::app.admin.companies.credit.amount')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                    type="text"
                                    name="amount"
                                    rules="required|decimal:0,4|min_value:0.01"
                                    :placeholder="max(0, (float) $credit->outstanding_balance)"
                                />
                                <x-admin::form.control-group.error control-name="amount" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>@lang('b2b::app.admin.companies.credit.reference')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="text" name="reference" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group class="!mb-0">
                                <x-admin::form.control-group.label>@lang('b2b::app.admin.companies.credit.comment')</x-admin::form.control-group.label>
                                <x-admin::form.control-group.control type="textarea" name="comment" rows="2" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <x-slot:footer>
                            <button type="submit" class="primary-button">@lang('b2b::app.admin.companies.credit.reimburse-btn')</button>
                        </x-slot>
                    </x-admin::modal>
                </x-admin::form>
        </div>
    </div>

    @php
        $companyName = ($companyFlat->business_name ?? null) ?: $company->name;
    @endphp

    <!-- Company details -->
    <div class="box-shadow mt-4 rounded bg-white p-4 dark:bg-gray-900">
        <p class="mb-3 text-base font-semibold text-gray-800 dark:text-white">@lang('b2b::app.admin.companies.credit.company-details')</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.company-name')</span>
                <p class="mt-1 font-medium text-gray-800 dark:text-white">{{ $companyName }}</p>
            </div>

            <div>
                <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.email')</span>
                <p class="mt-1 break-words text-gray-700 dark:text-gray-200">{{ ($companyFlat->email ?? null) ?: $company->email ?: '—' }}</p>
            </div>

            <div>
                <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.phone')</span>
                <p class="mt-1 text-gray-700 dark:text-gray-200">{{ ($companyFlat->phone ?? null) ?: '—' }}</p>
            </div>

            <div>
                <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.tax-id')</span>
                <p class="mt-1 text-gray-700 dark:text-gray-200">{{ ($companyFlat->vat_tax_id ?? null) ?: '—' }}</p>
            </div>
        </div>
    </div>

    <!-- Summary cards -->
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <!-- Row 1: Outstanding balance + Available credit -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.outstanding-balance')</span>
            <p class="mt-1 text-lg font-bold {{ (float) $credit->outstanding_balance > 0 ? 'text-red-600' : 'text-gray-800 dark:text-white' }}">{{ core()->formatBasePrice(max(0, (float) $credit->outstanding_balance)) }}</p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.available-credit')</span>
            <p class="mt-1 text-lg font-bold {{ $available > 0 ? 'text-green-600' : 'text-gray-800 dark:text-white' }}">{{ core()->formatBasePrice($available) }}</p>
        </div>

        <!-- Row 2: Credit limit + Status -->
        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.credit-limit')</span>
            <p class="mt-1 text-lg font-bold text-gray-800 dark:text-white">{{ core()->formatBasePrice($credit->credit_limit) }}</p>
        </div>

        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
            <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.companies.credit.status')</span>
            <p class="mt-1">
                <span class="{{ $credit->status ? 'label-active' : 'label-canceled' }}">
                    @lang('b2b::app.admin.companies.credit.'.($credit->status ? 'enabled' : 'disabled'))
                </span>
            </p>
        </div>
    </div>

    <!-- Credit history (ledger) — wrapped in its own card -->
    <div class="b2b-credit-history-card box-shadow mt-6 rounded bg-white p-4 dark:bg-gray-900">
        <p class="mb-3 text-base font-semibold text-gray-800 dark:text-white">@lang('b2b::app.admin.companies.credit.history')</p>

        <x-admin::datagrid
            :src="route('admin.b2b.companies.credit', $company->id)"
            :isMultiRow="true"
        >
            <!-- Header -->
            <template #header="{ isLoading, available, applied, sort }">
                <template v-if="isLoading">
                    <x-b2b::shimmer.datagrid.credit.head />
                </template>

                <template v-else>
                    <div class="b2b-credit-head border-b px-4 py-2.5 dark:border-gray-800">
                        <div
                            class="flex select-none items-center gap-2.5"
                            :class="{ 'b2b-credit-divider': columnGroupIndex > 0 }"
                            v-for="(columnGroup, columnGroupIndex) in [['created_at'], ['operation'], ['amount'], ['available_credit_after'], ['details']]"
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
                    <x-b2b::shimmer.datagrid.credit.body />
                </template>

                <template v-else>
                    <div
                        class="b2b-credit-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                        v-for="record in available.records"
                    >
                        <!-- Date -->
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 lg:hidden">
                                @lang('b2b::app.admin.companies.credit.date')
                            </span>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">@{{ record.created_at }}</p>
                        </div>

                        <!-- Operation -->
                        <div class="b2b-credit-divider flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 lg:hidden">
                                @lang('b2b::app.admin.companies.credit.operation')
                            </span>
                            <div v-html="record.operation"></div>
                        </div>

                        <!-- Amount -->
                        <div class="b2b-credit-divider flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 lg:hidden">
                                @lang('b2b::app.admin.companies.credit.amount')
                            </span>
                            <div class="text-sm" v-html="record.amount"></div>
                        </div>

                        <!-- Balance -->
                        <div class="b2b-credit-divider flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 lg:hidden">
                                @lang('b2b::app.admin.companies.credit.balance-after')
                            </span>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">@{{ record.available_credit_after }}</p>
                        </div>

                        <!-- Details -->
                        <div class="b2b-credit-divider flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500 lg:hidden">
                                @lang('b2b::app.admin.companies.credit.details')
                            </span>
                            <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300" v-html="record.details"></div>
                        </div>
                    </div>
                </template>
            </template>
        </x-admin::datagrid>
    </div>

    @pushOnce('styles')
        <style>
            /**
             * The datagrid renders its own bordered/box-shadow table card. Inside our own
             * history card that would read as a card-in-a-card, so flatten the datagrid's
             * wrapper and let it sit flush within the single history card.
             */
            .b2b-credit-history-card .table-responsive {
                border: 0;
                box-shadow: none;
                border-radius: 0;
            }

            /**
             * Reserve the ledger's vertical space up front so the card does not start short
             * and snap taller once the datagrid finishes loading over ajax.
             */
            .b2b-credit-history-card {
                min-height: 26rem;
            }

            /**
             * Responsive layout for the credit-ledger multi-row datagrid: one column on
             * mobile, five aligned columns on desktop. These grid utilities and the dividers
             * are purged out of the B2B admin bundle, so they live in this scoped block.
             */
            .b2b-credit-grid,
            .b2b-credit-head {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
            }

            .b2b-credit-grid {
                gap: 1rem;
            }

            .b2b-credit-head {
                display: none;
            }

            @media (min-width: 1024px) {
                .b2b-credit-grid,
                .b2b-credit-head {
                    grid-template-columns: minmax(0, 1.1fr) minmax(8.5rem, 1fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.6fr);
                    column-gap: 1.5rem;
                }

                .b2b-credit-grid {
                    row-gap: 0;
                    align-items: start;
                }

                .b2b-credit-head {
                    display: grid;
                    align-items: center;
                }

                .b2b-credit-divider {
                    border-inline-start: 1px solid rgb(243 244 246);
                    padding-inline-start: 1.5rem;
                }

                .dark .b2b-credit-divider {
                    border-inline-start-color: rgb(31 41 55);
                }
            }
        </style>
    @endPushOnce
</x-admin::layouts>
