<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex items-center justify-between p-4">
        <p class="text-base font-semibold text-gray-800 dark:text-white">
            @lang('b2b_suite::app.admin.quotes.create.account-information-title')
        </p>

        <button
            class="secondary-button"
            @click="$refs.selectCompanyComponent.openDrawer()"
        >
            @lang('b2b_suite::app.admin.quotes.create.edit-company')
        </button>
    </div>

    <div class="grid">
        <div class="flex flex-1 flex-col px-4">
            <!-- Company Details Section -->
            <div class="flex justify-between bg-gray-100 p-4 dark:bg-gray-800">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    @lang('b2b_suite::app.admin.quotes.create.company-name')
                </span>
                
                <div class="cursor-pointer text-blue-600 text-gray-900 transition-all hover:underline dark:text-white">
                    <a href="{{ route('admin.customers.companies.edit', $company->id) }}">{{ $company->name ?? '-' }}</a>
                </div>
            </div>

            <div class="flex justify-between p-4">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    @lang('b2b_suite::app.admin.quotes.create.company-email')
                </span>
                
                <div class="text-gray-900 dark:text-white">{{ $company->email ?? '-' }}</div>
            </div>

            <div class="flex justify-between bg-gray-100 p-4 dark:bg-gray-800">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    @lang('b2b_suite::app.admin.quotes.create.company-phone')
                </span>
                
                <div class="text-gray-900 dark:text-white">{{ $company->phone ?? '-' }}</div>
            </div>
            
            <div class="flex justify-between p-4">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    @lang('b2b_suite::app.admin.quotes.create.agent-name')
                </span>
                
                <div class="cursor-pointer text-blue-600 text-gray-900 transition-all hover:underline dark:text-white">
                    <a href="{{ route('admin.account.edit') }}">{{ $user->name ?? '-' }}</a>
                </div>
            </div>

            <div class="flex justify-between bg-gray-100 p-4 dark:bg-gray-800">
                <span class="font-medium text-gray-600 dark:text-gray-300">
                    @lang('b2b_suite::app.admin.quotes.create.agent-email')
                </span>
                
                <div class="text-gray-900 dark:text-white">{{ $user->email ?? '-' }}</div>
            </div>
        </div>
    </div>
</div>

<v-company-search ref="selectCompanyComponent"></v-company-search>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-company-search-template"
    >
        <div class="">
            <!-- Search Drawer -->
            <x-admin::drawer
                ref="searchCompanyDrawer"
                @close="searchTerm = ''; searchedCompanies = [];"
            >
                <!-- Drawer Header -->
                <x-slot:header>
                    <div class="grid gap-3">
                        <p class="py-2 text-xl font-medium dark:text-white">
                            @lang('b2b_suite::app.admin.quotes.create.search-company')
                        </p>

                        <div class="relative w-full">
                            <input
                                type="text"
                                class="block w-full rounded-lg border bg-white py-1.5 leading-6 text-gray-600 transition-all hover:border-gray-400 ltr:pl-3 ltr:pr-10 rtl:pl-10 rtl:pr-3 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                                placeholder="@lang('b2b_suite::app.admin.quotes.create.search-by')"
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
                        v-if="searchedCompanies.length"
                    >
                        <div
                            class="grid cursor-pointer place-content-start gap-1.5 border-b border-slate-300 p-4 last:border-b-0 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-gray-950"
                            v-for="company in searchedCompanies"
                            @click="selectCompany(company)"
                        >
                            <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
                                @{{ company.first_name + ' ' + company.last_name }}
                            </p>

                            <p class="text-gray-500">
                                @{{ company.email }}
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
                                @lang('b2b_suite::app.admin.quotes.create.empty-title')
                            </p>

                            <p class="text-gray-400">
                                @lang('b2b_suite::app.admin.quotes.create.empty-info')
                            </p>
                        </div>
                    </div>
                </x-slot>
            </x-admin::drawer>

            <v-create-customer-form
                ref="createCompanyComponent"
                @customer-created="createCart"
            ></v-create-customer-form>
        </div>
    </script>

    <script type="module">
        app.component('v-company-search', {
            template: '#v-company-search-template',

            data() {
                return {
                    cart: @json($cart),

                    searchTerm: '',

                    searchedCompanies: [],

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
                    this.$refs.searchCompanyDrawer.open();
                },

                search() {
                    if (this.searchTerm.length <= 1) {
                        this.searchedCompanies = [];

                        return;
                    }

                    this.isSearching = true;

                    let self = this;

                    this.$axios.get("{{ route('admin.customers.companies.search') }}", {
                            params: {
                                query: this.searchTerm,
                                type: 'company'
                            }
                        })
                        .then(function(response) {
                            self.isSearching = false;

                            self.searchedCompanies = response.data.data;
                        })
                        .catch(function (error) {
                        });
                },

                selectCompany(company) {
                    this.$axios.post("{{ route('admin.customers.cart.store') }}", {cart_id: this.cart.id, company_id: company.id})
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