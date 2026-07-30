<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.admin.quotes.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="py-3 text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.quotes.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.b2b.quotes.index') }}" />
            
            {!! view_render_event('bagisto.admin.b2b.quotes.create.before') !!}

            @if (bouncer()->hasPermission('b2b.quotes.create'))
                <button
                    class="primary-button"
                    @click="$refs.selectCustomerComponent.openDrawer()"
                >
                    @lang('b2b::app.admin.quotes.index.create-btn')
                </button>
            @endif

            {!! view_render_event('bagisto.admin.b2b.quotes.create.after') !!}
        </div>
    </div>

    <v-customer-search ref="selectCustomerComponent"></v-customer-search>

    <x-admin::datagrid :src="route('admin.b2b.quotes.index')" :isMultiRow="true">
        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-b2b::shimmer.datagrid.dg.head />
            </template>

            <template v-else>
                <div class="b2b-dg-head b2b-datagrid-head">
                    <div
                        class="flex select-none items-center gap-2.5"
                        :class="{ 'b2b-dg-divider': index > 0 }"
                        v-for="(columnGroup, index) in [['quotation_number', 'name', 'status'], ['company_name', 'agent_name', 'customer_name', 'created_at'], ['base_total', 'negotiated_total', 'expiration_date'], ['items']]"
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
                <x-b2b::shimmer.datagrid.dg.body />
            </template>

            <template v-else>
                <div
                    class="b2b-dg-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Quote Id, Name, Status -->
                    <div class="flex flex-col gap-2">
                        <a
                            class="w-fit text-base font-semibold text-gray-800 transition-all hover:text-blue-600 dark:text-white"
                            :href=`{{ route('admin.b2b.quotes.index') }}/${record.quote_id}`
                        >
                            @{{ "@lang('b2b::app.admin.quotes.index.datagrid.id')".replace(':id', record.quotation_number) }}
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
                            :href=`{{ route('admin.b2b.quotes.index') }}/${record.quote_id}`
                            title="@lang('b2b::app.admin.quotes.index.datagrid.view')"
                        >
                            <span class="icon-view text-2xl"></span>
                        </a>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>
    
    @include('admin::customers.customers.index.create')

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

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-customer-search-template"
        >
            <div class="">
                <!-- Search Drawer -->
                <x-admin::drawer
                    ref="searchCustomerDrawer"
                    @close="searchTerm = ''; searchedCustomers = [];"
                >
                    <!-- Drawer Header -->
                    <x-slot:header>
                        <div class="grid gap-3">
                            <p class="py-2 text-xl font-medium dark:text-white">
                                @lang('admin::app.sales.orders.index.search-customer.title')
                            </p>

                            <div class="relative w-full">
                                <input
                                    type="text"
                                    class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 ltr:pl-3 ltr:pr-10 rtl:pl-10 rtl:pr-3 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                    placeholder="@lang('admin::app.sales.orders.index.search-customer.search-by')"
                                    v-model.lazy="searchTerm"
                                    v-debounce="500"
                                />

                                <template v-if="isSearching">
                                    <img
                                        class="absolute top-2.5 h-5 w-5 animate-spin ltr:right-3 rtl:left-3"
                                        src="{{ bagisto_asset('images/spinner.svg') }}"
                                    />
                                </template>

                                <template v-else>
                                    <span class="icon-search pointer-events-none absolute top-1.5 flex items-center text-2xl ltr:right-3 rtl:left-3"></span>
                                </template>
                            </div>
                        </div>
                    </x-slot>

                    <!-- Drawer Content -->
                    <x-slot:content class="!p-0">
                        <div
                            class="grid max-h-[400px] overflow-y-auto"
                            v-if="searchedCustomers.length"
                        >
                            <div
                                class="grid cursor-pointer place-content-start gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                                v-for="customer in searchedCustomers"
                                @click="createCart(customer)"
                            >
                                <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                    @{{ customer.first_name + ' ' + customer.last_name }}
                                </p>

                                <p class="text-gray-500">
                                    @{{ customer.email }}
                                </p>
                            </div>
                        </div>

                        <!-- For Empty Variations -->
                        <div
                            class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                            v-else
                        >
                            <!-- Placeholder Image -->
                            <img
                                src="{{ bagisto_asset('images/empty-placeholders/customers.svg') }}"
                                class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                            />

                            <!-- Add Variants Information -->
                            <div class="flex flex-col items-center gap-1.5">
                                <p class="text-base font-semibold text-gray-400">
                                    @lang('admin::app.sales.orders.index.search-customer.empty-title')
                                </p>

                                <p class="text-gray-400">
                                    @lang('admin::app.sales.orders.index.search-customer.empty-info')
                                </p>

                                <button
                                    class="secondary-button"
                                    @click="$refs.searchCustomerDrawer.close(); $refs.createCustomerComponent.openModal()"
                                >
                                    @lang('admin::app.sales.orders.index.search-customer.create-btn')
                                </button>
                            </div>
                        </div>
                    </x-slot>
                </x-admin::drawer>

                <v-create-customer-form
                    ref="createCustomerComponent"
                    @customer-created="createCart"
                ></v-create-customer-form>
            </div>
        </script>

        <script type="module">
            app.component('v-customer-search', {
                template: '#v-customer-search-template',

                data() {
                    return {
                        searchTerm: '',

                        searchedCustomers: [],

                        isSearching: false,
                    }
                },

                watch: {
                    searchTerm: function(newVal, oldVal) {
                        this.search();
                    }
                },

                methods: {
                    openDrawer() {
                        this.$refs.searchCustomerDrawer.open();
                    },

                    search() {
                        if (this.searchTerm.length <= 1) {
                            this.searchedCustomers = [];

                            return;
                        }

                        this.isSearching = true;

                        let self = this;

                        this.$axios.get("{{ route('admin.b2b.companies.search') }}", {
                                params: {
                                    query: this.searchTerm,
                                    type: 'user'
                                }
                            })
                            .then(function(response) {
                                self.isSearching = false;

                                self.searchedCustomers = response.data.data;
                            })
                            .catch(function (error) {
                            });
                    },

                    createCart(customer) {
                        this.$axios.post("{{ route('admin.b2b.cart.store') }}", {customer_id: customer.id})
                            .then(function(response) {
                                window.location.href = response.data.redirect_url;
                            })
                            .catch(function (error) {
                                this.$emitter.emit('add-flash', { type: 'error', message: error.response.data.message });
                            });
                    },
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
