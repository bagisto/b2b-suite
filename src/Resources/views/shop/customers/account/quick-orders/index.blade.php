<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.quick-orders.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="quick-orders" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="flex-auto max-md:px-4">

        {!! view_render_event('bagisto.shop.customers.account.quick_orders.before') !!}

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-2.5">
                <!-- Back Button (mobile) -->
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.profile.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base">
                    @lang('b2b::app.shop.customers.account.quick-orders.title')
                </h2>
            </div>

            <!-- Back Button (desktop) -->
            <a
                href="{{ route('shop.customers.account.profile.index') }}"
                class="transparent-button px-5 py-2.5 hover:bg-gray-100 max-md:hidden"
            >
                @lang('b2b::app.shop.customers.account.quick-orders.btn-back')
            </a>
        </div>

        <!-- Quick Order Component -->
        <div class="mt-6 max-md:mt-4">
            <v-quick-order ref="vQuickOrder"></v-quick-order>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.quick_orders.after') !!}

    </div>

    @pushOnce('scripts')
        @php
            $maxFileSize = (int) (core()->getConfigData('b2b.quotes.settings.maximum_file_size') ?: 2);
        @endphp

        <!-- Vue Template -->
        <script type="text/x-template" id="v-quick-order-template">
            <div class="grid gap-6 max-md:gap-4">
                <!-- Add Products By Search -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 max-md:p-4">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="icon-search grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-50 text-xl text-blue-600 dark:bg-gray-800 dark:text-blue-400"></span>

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            @lang('b2b::app.shop.customers.account.quick-orders.search-by-sku-name')
                        </h3>
                    </div>

                    <!-- Search Input -->
                    <div class="relative w-full">
                        <input
                            type="text"
                            class="w-full rounded-lg border border-zinc-300 px-5 py-3 text-base text-gray-700 outline-none transition-all focus:border-navyBlue dark:border-gray-700 dark:bg-gray-900 dark:text-white ltr:pr-12 rtl:pl-12"
                            placeholder="@lang('b2b::app.shop.customers.account.quick-orders.search-by-sku-name')"
                            v-model.lazy="searchTerm"
                            v-debounce="500"
                        />

                        <template v-if="isSearching">
                            <img
                                class="absolute top-3.5 h-5 w-5 animate-spin ltr:right-4 rtl:left-4"
                                src="{{ bagisto_asset('images/spinner.svg') }}"
                            />
                        </template>

                        <template v-else>
                            <span class="icon-search absolute top-2.5 text-2xl text-gray-400 ltr:right-3 rtl:left-3"></span>
                        </template>
                    </div>

                    <!-- Search Results -->
                    <div v-if="searchedProducts.length" class="mt-4 grid gap-2.5">
                        <div
                            v-for="product in searchedProducts"
                            :key="product.id"
                            class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 transition-all hover:border-zinc-300 hover:shadow-sm dark:border-gray-800"
                        >
                            <!-- Information -->
                            <div class="flex items-center gap-3">
                                <!-- Image -->
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-zinc-100 dark:border-gray-800">
                                    <template v-if="! product.images.length">
                                        <img
                                            class="h-16 w-16 object-cover"
                                            src="{{ bagisto_asset('images/small-product-placeholder.webp') }}"
                                        >
                                    </template>

                                    <template v-else>
                                        <img
                                            class="h-16 w-16 object-cover"
                                            :src="product.images[0].url"
                                        >
                                    </template>
                                </div>

                                <!-- Details -->
                                <div class="grid gap-0.5">
                                    <p class="break-all text-sm font-medium text-gray-900 dark:text-white">
                                        @{{ product.name }}
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        @lang('b2b::app.shop.customers.account.quick-orders.sku') @{{ product.sku }}
                                    </p>

                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        @{{ product.formatted_price }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action -->
                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-1 rounded-lg border border-navyBlue px-4 py-2 text-sm font-medium text-navyBlue transition-all hover:bg-navyBlue hover:text-white max-sm:px-3"
                                @click="addProduct(product)"
                            >
                                <span class="icon-plus text-lg"></span>

                                <span class="max-sm:hidden">
                                    @lang('b2b::app.shop.customers.account.quick-orders.btn-add')
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bulk Add: Multiple SKUs & Upload File -->
                <div class="grid grid-cols-2 gap-6 max-md:grid-cols-1 max-md:gap-4">
                    <!-- Multiple SKUs -->
                    <div class="flex flex-col rounded-xl border border-zinc-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 max-md:p-4">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="icon-listing grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-50 text-xl text-blue-600 dark:bg-gray-800 dark:text-blue-400"></span>

                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                @lang('b2b::app.shop.customers.account.quick-orders.enter-multiple-skus')
                            </h3>
                        </div>

                        <x-shop::form.control-group>
                            <x-shop::form.control-group.control
                                type="textarea"
                                rows="4"
                                v-model="multipleSKUs"
                                :placeholder="trans('b2b::app.shop.customers.account.quick-orders.enter-multiple-skus')"
                            />
                        </x-shop::form.control-group>

                        <button
                            type="button"
                            class="secondary-button mt-auto rounded-lg px-6 py-2.5 text-sm"
                            v-if="multipleSKUs.length"
                            @click="addMultipleSKUs()"
                        >
                            @lang('b2b::app.shop.customers.account.quick-orders.btn-add-to-list')
                        </button>
                    </div>

                    <!-- Upload A File -->
                    <div class="flex flex-col rounded-xl border border-zinc-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 max-md:p-4">
                        <div class="mb-4 flex items-center gap-3">
                            <span class="icon-download grid h-10 w-10 shrink-0 place-items-center rounded-full bg-blue-50 text-xl text-blue-600 dark:bg-gray-800 dark:text-blue-400"></span>

                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                @lang('b2b::app.shop.customers.account.quick-orders.upload-file')
                            </h3>
                        </div>

                        <x-shop::form.control-group>
                            <x-shop::form.control-group.control
                                type="file"
                                name="upload_file"
                                :rules="'mimes:csv|ext:csv|size:{{ $maxFileSize * 1024 }}'"
                                :placeholder="trans('b2b::app.shop.customers.account.quick-orders.upload-file')"
                                @change="handleFileUpload"
                            />
                        </x-shop::form.control-group>

                        <div class="mt-2 flex flex-wrap items-center gap-1 text-sm text-gray-500">
                            <span>@lang('b2b::app.shop.customers.account.quick-orders.add-from-file')</span>

                            <a
                                href="{{ route('shop.customers.account.quick_orders.downloadSample') }}"
                                class="inline-flex items-center gap-1 font-medium text-blue-600 hover:underline"
                            >
                                <span class="icon-download text-base"></span>

                                @lang('b2b::app.shop.customers.account.quick-orders.download-sample')
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Selected Products -->
                <div
                    v-if="selectedProducts.length"
                    class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-gray-800 dark:bg-gray-900"
                >
                    <!-- Header -->
                    <div class="flex items-center gap-2 border-b border-zinc-200 px-6 py-4 dark:border-gray-800 max-md:px-4">
                        <span class="icon-cart text-xl text-gray-700 dark:text-gray-200"></span>

                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                            @lang('b2b::app.shop.customers.account.quick-orders.selected-products') (@{{ selectedProducts.length }})
                        </h3>
                    </div>

                    <!-- Items -->
                    <div
                        v-for="(product, index) in selectedProducts"
                        :key="product.id"
                        class="flex items-center justify-between gap-3 border-b border-zinc-100 px-6 py-4 transition-all last:border-b-0 hover:bg-gray-50/60 dark:border-gray-800 max-md:flex-wrap max-md:px-4"
                    >
                        <!-- Information -->
                        <div class="flex items-center gap-3">
                            <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-zinc-100 dark:border-gray-800">
                                <template v-if="! product.images.length">
                                    <img
                                        class="h-16 w-16 object-cover"
                                        src="{{ bagisto_asset('images/small-product-placeholder.webp') }}"
                                    >
                                </template>

                                <template v-else>
                                    <img
                                        class="h-16 w-16 object-cover"
                                        :src="product.images[0].url"
                                    >
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid gap-0.5">
                                <p class="break-all text-sm font-medium text-gray-900 dark:text-white">
                                    @{{ product.name }}
                                </p>

                                <p class="text-xs text-gray-500">
                                    @lang('b2b::app.shop.customers.account.quick-orders.sku') @{{ product.sku }}
                                </p>

                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    @{{ product.formatted_price }}
                                </p>
                            </div>
                        </div>

                        <!-- Qty + Remove -->
                        <div class="flex items-center gap-3">
                            <x-shop::quantity-changer
                                class="flex max-w-max items-center gap-x-2.5 rounded-lg border border-zinc-300 px-3 py-1.5 dark:border-gray-700"
                                name="quantity"
                                ::value="product.qty"
                                @change="product.qty = $event"
                            />

                            <button
                                type="button"
                                class="grid h-9 w-9 place-items-center rounded-lg text-lg text-gray-400 transition-all hover:bg-red-50 hover:text-red-600"
                                @click="removeProduct(product.id)"
                                aria-label="@lang('b2b::app.shop.customers.account.quick-orders.btn-add')"
                            >
                                <span class="icon-cancel"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div
                    class="flex justify-end max-md:justify-stretch"
                    v-if="selectedProducts.length || uploadFile"
                >
                    <button
                        type="button"
                        class="primary-button rounded-lg px-10 py-3 max-md:w-full max-md:place-content-center"
                        @click="submitList()"
                    >
                        @lang('b2b::app.shop.customers.account.quick-orders.btn-add-to-cart')
                    </button>
                </div>
            </div>
        </script>

        <!-- Vue Component -->
        <script type="module">
            app.component("v-quick-order", {
                template: '#v-quick-order-template',

                data() {
                    return {
                        searchTerm: '',
                        multipleSKUs: '',
                        searchedProducts: [],
                        selectedProducts: [],
                        isSearching: false,
                        uploadFile: null,
                        maxFileSizeMB: '{{ $maxFileSize }}',
                    }
                },

                watch: {
                    searchTerm: function () {
                        this.search();
                    }
                },

                methods: {
                    handleFileUpload($event) {
                        this.uploadFile = $event.target.files[0] || null;
                    },
                    
                    search() {
                        if (this.searchTerm.length <= 1) {
                            this.searchedProducts = [];
                            return;
                        }

                        this.isSearching = true;

                        this.$axios.get("{{ route('shop.customers.account.quick_orders.search') }}", {
                            params: { query: this.searchTerm, limit: 5 }
                        })
                        .then((response) => {
                            this.isSearching = false;

                            // filter out already selected products
                            const selectedIds = this.selectedProducts.map(p => p.id);
                            this.searchedProducts = response.data.data.filter(
                                p => !selectedIds.includes(p.id)
                            );
                        })
                        .catch(() => { this.isSearching = false; });
                    },

                    addProduct(product) {
                        if (!this.selectedProducts.find(p => p.id === product.id)) {
                            this.selectedProducts.push({ ...product, qty: 1 });
                        }

                        // remove from search results
                        this.searchedProducts = this.searchedProducts.filter(
                            p => p.id !== product.id
                        );
                    },

                    addMultipleSKUs() {
                        if (!this.multipleSKUs.trim()) return;

                        const skus = this.multipleSKUs.split(",")
                                     .map(s => s.trim()).filter(s => s);

                        this.$axios.post("{{ route('shop.customers.account.quick_orders.fetchBySkus') }}", {
                            skus: skus
                        })
                        .then((response) => {
                            response.data.data.forEach(product => {
                                if (!this.selectedProducts.find(p => p.id === product.id)) {
                                    this.selectedProducts.push({ ...product, qty: 1 });
                                }
                            });

                            this.multipleSKUs = '';
                        });
                    },

                    removeProduct(productId) {
                        this.selectedProducts = this.selectedProducts.filter(p => p.id !== productId);

                        if (this.searchTerm.length > 1) {
                            this.search();
                        }
                    },

                    submitList() {
                        if (!this.selectedProducts.length && !this.uploadFile) {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: '@lang("b2b::app.shop.checkout.cart.request-failed")' 
                            });
                            return;
                        }

                        const formData = new FormData();
                            
                        if (this.selectedProducts.length > 0) {
                            // formData.append("products", JSON.stringify(
                            //     this.selectedProducts.map(p => ({
                            //         id: p.id,
                            //         qty: p.qty
                            //     }))
                            // ));

                            this.selectedProducts.forEach((product, index) => {
                                formData.append(`products[${index}][sku]`, product.sku);
                                formData.append(`products[${index}][quantity]`, product.qty);
                            });
                        }
                        
                        if (this.uploadFile) {
                            const allowedTypes = ["text/csv", "application/csv", "application/vnd.ms-excel"];
                            const fileExtension = this.uploadFile.name.split('.').pop().toLowerCase();

                            if (!allowedTypes.includes(this.uploadFile.type) && fileExtension !== 'csv') {
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: '@lang("b2b::app.shop.checkout.cart.invalid-file-type")' 
                                });
                                return;
                            }

                            const maxSizeBytes = this.maxFileSizeMB * 1024 * 1024;
                            if (this.uploadFile.size > maxSizeBytes) {
                                this.$emitter.emit('add-flash', { 
                                    type: 'error', 
                                    message: '@lang("b2b::app.shop.checkout.cart.file-size-exceeds")'.replace(':size', this.maxFileSizeMB) 
                                });
                                return;
                            }
                            
                            formData.append("upload_file", this.uploadFile);
                        }

                        this.$axios.post("{{ route('shop.customers.account.quick_orders.store') }}", formData, {
                            headers: {
                                'Content-Type': 'multipart/form-data'
                            }
                        })
                        .then((response) => {
                                if (response.data.status) {
                                    if (response.data.message) {
                                        this.$emitter.emit('add-flash', { 
                                            type: 'success', 
                                            message: response.data.message 
                                        });
                                    }
                                    if (response.data.redirect_url) {
                                        window.location.href = response.data.redirect_url;
                                    }
                                } else if (response.data.message) {
                                    this.$emitter.emit('add-flash', { 
                                        type: 'error', 
                                        message: response.data.message 
                                    });
                                }
                            })
                        .catch((error) => {
                            this.$emitter.emit('add-flash', { 
                                type: 'error', 
                                message: error.response?.data?.message || '@lang("b2b::app.shop.checkout.cart.request-failed")' 
                            });
                        });
                    }
                }
            });
        </script>
    @endPushOnce
</x-shop::layouts.account>
