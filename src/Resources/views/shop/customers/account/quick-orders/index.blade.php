<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.quick-orders.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="profile.edit" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="flex-auto">
    
        {!! view_render_event('bagisto.shop.customers.account.quick_orders.before') !!}

        <!-- Page Header -->
        <div class="grid gap-2.5">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <h2 class="text-2xl font-medium">
                    @lang('b2b_suite::app.shop.customers.account.quick-orders.title')
                </h2>

                <div class="flex items-center gap-x-2.5">
                    <!-- Back Button -->
                    <a
                        href="{{ route('shop.customers.account.profile.index') }}"
                        class="transparent-button px-5 py-2.5"
                    >
                        @lang('b2b_suite::app.shop.customers.account.quick-orders.btn-back')
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Order Component -->
        <div class="container mt-4 px-[60px] max-lg:px-8 max-md:px-4">
            <v-quick-order ref="vQuickOrder"></v-quick-order>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.quick_orders.after') !!}
    
    </div>

    @pushOnce('scripts')
        <!-- Vue Template -->
        <script type="text/x-template" id="v-quick-order-template">
            <div class="grid gap-12">
                <!-- Search Box -->
                <div class="mb-4 grid">
                    <p class="required mb-2 block text-base">
                        @lang('b2b_suite::app.shop.customers.account.quick-orders.search-by-sku-name')
                    </p>

                    <div class="relative w-full">
                        <input
                            type="text"
                            class="mb-1.5 w-full rounded-lg border px-5 py-3 text-base text-gray-600"
                            placeholder="@lang('b2b_suite::app.shop.customers.account.quick-orders.search-by-sku-name')"
                            v-model.lazy="searchTerm"
                            v-debounce="500"
                        />

                        <template v-if="isSearching">
                            <img class="absolute top-2.5 h-5 w-5 animate-spin ltr:right-3 rtl:left-3"
                                 src="{{ bagisto_asset('images/spinner.svg') }}" />
                        </template>

                        <template v-else>
                            <span class="icon-search absolute top-1.5 flex items-center text-2xl ltr:right-3 rtl:left-3"></span>
                        </template>
                    </div>
                </div>

                <!-- Search Results -->
                <div v-if="searchedProducts.length" class="mt-4 grid gap-5">
                    <div
                        v-for="product in searchedProducts"
                        :key="product.id"
                        class="my-4 flex justify-between gap-2.5 border-b border-slate-300 px-4 py-6"
                    >
                        <!-- Information -->
                        <div class="flex gap-2.5">
                            <!-- Image -->
                            <div class="relative h-[100px] w-[100px] overflow-hidden rounded border border-gray-200">
                                <template v-if="! product.images.length">
                                    <img
                                        class="h-[100px] w-[100px] rounded"
                                        src="{{ bagisto_asset('images/small-product-placeholder.webp') }}"
                                    >
                                    <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                        @lang('b2b_suite::app.shop.customers.account.quick-orders.product-image')
                                    </p>
                                </template>
                                <template v-else>
                                    <img
                                        class="h-[100px] w-[100px] rounded object-cover"
                                        :src="product.images[0].url"
                                    >
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid place-content-start gap-1.5">
                                <p class="break-all text-base font-semibold text-gray-800">
                                    @{{ product.name }}
                                </p>
                                <p class="text-gray-600">SKU: @{{ product.sku }}</p>
                                <p class="font-semibold text-gray-800">@{{ product.formatted_price }}</p>
                            </div>
                        </div>

                        <!-- Action -->
                        <div class="flex items-center">
                            <button
                                type="button"
                                class="primary-button px-5 py-2.5"
                                @click="addProduct(product)"
                            >
                                @lang('b2b_suite::app.shop.customers.account.quick-orders.btn-add')
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selected Products -->
                <div v-if="selectedProducts.length" class="mt-6 rounded-lg border p-4">
                    <h3 class="mb-3 font-semibold">Selected Products</h3>

                    <div
                        v-for="(product, index) in selectedProducts"
                        :key="product.id"
                        class="my-4 flex justify-between gap-2.5 border-b border-slate-300 px-4 py-6"
                    >
                        <!-- Information -->
                        <div class="flex gap-2.5">
                            <div class="relative h-[100px] w-[100px] overflow-hidden rounded border border-gray-200">
                                <template v-if="! product.images.length">
                                    <img
                                        class="h-[100px] w-[100px] rounded"
                                        src="{{ bagisto_asset('images/small-product-placeholder.webp') }}"
                                    >
                                    <p class="absolute bottom-1.5 w-full text-center text-[6px] font-semibold text-gray-400">
                                        @lang('b2b_suite::app.shop.customers.account.quick-orders.product-image')
                                    </p>
                                </template>
                                <template v-else>
                                    <img
                                        class="h-[100px] w-[100px] rounded object-cover"
                                        :src="product.images[0].url"
                                    >
                                </template>
                            </div>

                            <!-- Details -->
                            <div class="grid place-content-start gap-1.5">
                                <p class="break-all text-base font-semibold text-gray-800">
                                    @{{ product.name }}
                                </p>
                                <p class="text-gray-600">SKU: @{{ product.sku }}</p>
                                <p class="font-semibold text-gray-800">@{{ product.formatted_price }}</p>
                            </div>
                        </div>

                        <!-- Qty + Remove -->
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                min="1"
                                v-model.number="product.qty"
                                class="w-20 rounded border px-2 py-1"
                            />
                            <button
                                type="button"
                                class="text-red-500"
                                @click="removeProduct(product.id)"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Multiple SKUs -->
                <div class="mt-6">
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label>
                            @lang('b2b_suite::app.shop.customers.account.quick-orders.enter-multiple-skus')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="textarea"
                            rows="4"
                            v-model="multipleSKUs"
                            :placeholder="trans('b2b_suite::app.shop.customers.account.quick-orders.enter-multiple-skus')"
                        />
                    </x-shop::form.control-group>

                    <button
                        type="button"
                        class="primary-button mt-3 px-6 py-2"
                        v-if="multipleSKUs.length"
                        @click="addMultipleSKUs()"
                    >
                        @lang('b2b_suite::app.shop.customers.account.quick-orders.btn-add-to-list')
                    </button>
                </div>

                <!-- Upload A File -->
                <div class="mt-6">
                    @php
                        $maxFileSize = (int) (core()->getConfigData('b2b_suite.quotes.settings.maximum_file_size') ?: 2);
                    @endphp
                    
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label>
                            @lang('b2b_suite::app.shop.customers.account.quick-orders.upload-file')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="file"
                            name="upload_file"
                            :rules="'mimes:csv|ext:csv|size:{{ $maxFileSize * 1024 }}'"
                            :placeholder="trans('b2b_suite::app.shop.customers.account.quick-orders.upload-file')"
                            @change="handleFileUpload"
                        />
                    </x-shop::form.control-group>
                    
                    <div class="flex gap-1">
                        <span>@lang('b2b_suite::app.shop.customers.account.quick-orders.add-from-file')</span>
                        <a href="{{ route('shop.customers.account.quick_orders.downloadSample') }}" class="text-blue-600 underline">
                            @lang('b2b_suite::app.shop.customers.account.quick-orders.download-sample')
                        </a>
                    </div>
                </div>

                <!-- Submit Button -->
                <div 
                    class="mt-6 text-right"
                    v-if="selectedProducts.length || uploadFile"
                >
                    <button
                        type="button"
                        class="primary-button px-10 py-3"
                        @click="submitList()"
                    >
                        @lang('b2b_suite::app.shop.customers.account.quick-orders.btn-add-to-cart')
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
                            alert("Please add products to the list or upload a file.");
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
                                alert("Invalid file type. Only CSV files are allowed.");
                                return;
                            }

                            const maxSizeBytes = this.maxFileSizeMB * 1024 * 1024;
                            if (this.uploadFile.size > maxSizeBytes) {
                                alert(`File size exceeds ${this.maxFileSizeMB} MB.`);
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
                            if (response.data.redirect_url) {
                                window.location.href = response.data.redirect_url;
                            }
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                    }
                }
            });
        </script>
    @endPushOnce
</x-shop::layouts.account>
