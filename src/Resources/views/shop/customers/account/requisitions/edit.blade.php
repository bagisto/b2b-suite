<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.requisitions.edit.title' , ['id' => $requisition->id])
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
    
        {!! view_render_event('bagisto.shop.customers.account.requisition.edit.before', ['requisition' => $requisition]) !!}

        <!-- Page Header -->
        <div class="grid gap-2.5">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    @lang('b2b_suite::app.shop.customers.account.requisitions.edit.title' , ['id' => $requisition->id])
                
                    <!-- Default Label -->
                    @if ($requisition->is_default)
                        <span class="label-pending p-2 text-white ltr:ml-2.5 rtl:mr-2.5">
                            @lang('b2b_suite::app.shop.customers.account.requisitions.edit.default-label')
                        </span>
                    @endif
                </h2>

                <div class="flex items-center gap-x-2.5">
                    <!-- Back Button -->
                    <a
                        href="{{ route('shop.customers.account.requisitions.index') }}"
                        class="transparent-button px-5 py-2.5"
                    >
                        @lang('b2b_suite::app.shop.customers.account.requisitions.edit.btn-back')
                    </a>
                </div>
            </div>
        </div>

        <div class="container mt-4 px-[60px] max-lg:px-8 max-md:px-4">
            <v-requisition-lists ref="vRequisition">
                <x-shop::shimmer.checkout.cart :count="3" />    
            </v-requisition-lists>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.requisition.edit.after', ['requisition' => $requisition]) !!}
    
    </div>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-requisition-lists-template"
        >
            <div>
                <div class="mt-4 border-b border-zinc-200 pb-6">
                    <div class="flex flex-wrap justify-between gap-2">
                        <h2 
                            class="text-xl font-medium"
                            v-text="requisition.name"
                        ></h2>

                        <!-- Update Create Option Item Modal -->
                        <div
                            class="cursor-pointer text-sm font-medium text-blue-600 hover:underline"
                            @click="$refs.updateRequisitionModal.open()"
                        >
                            @lang('b2b_suite::app.shop.customers.account.requisitions.edit.link-rename')
                        </div>
                    </div>

                    <p 
                        class="mt-2 text-sm text-gray-600"
                        v-text="requisition.description"
                    >
                    </p>
                </div>

                <!-- Add Option Form Modal -->
                <x-shop::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form 
                        @submit="handleSubmit($event, updateRequisition)"
                        ref="updateRequisitionForm"
                    >
                        <x-shop::modal ref="updateRequisitionModal">
                            <!-- Option Form Modal Header -->
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('b2b_suite::app.shop.customers.account.requisitions.edit.edit-title')
                                </p>
                            </x-slot>

                            <!-- Option Form Modal Content -->
                            <x-slot:content>

                                <x-shop::form.control-group.control
                                    type="hidden"
                                    name="requisition_id"
                                    ::value="requisition.id"
                                />
                                
                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required">
                                        @lang('b2b_suite::app.shop.customers.account.requisitions.edit.name')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        ::value="requisition.name"
                                        :label="trans('b2b_suite::app.shop.customers.account.requisitions.edit.name')"
                                    />

                                    <x-shop::form.control-group.error control-name="name" />
                                </x-shop::form.control-group>

                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required">
                                        @lang('b2b_suite::app.shop.customers.account.requisitions.edit.description')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="textarea"
                                        name="description"
                                        rows="4"
                                        rules="required"
                                        ::value="requisition.description"
                                        :label="trans('b2b_suite::app.shop.customers.account.requisitions.edit.description')"
                                        :placeholder="trans('b2b_suite::app.shop.customers.account.requisitions.edit.description')"
                                    />

                                    <x-shop::form.control-group.error control-name="description" />
                                </x-shop::form.control-group>
                            
                                <!-- Is Default -->
                                <div class="mb-5 flex select-none items-center gap-1.5">
                                    <input
                                        type="checkbox"
                                        name="is_default"
                                        id="is-default"
                                        class="peer hidden"
                                        :checked="requisition.is_default"
                                    />

                                    <label
                                        class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                        for="is-default"
                                    ></label>

                                    <label
                                        class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm ltr:pl-0 rtl:pr-0"
                                        for="is-default"
                                    >
                                        @lang('b2b_suite::app.shop.customers.account.requisitions.edit.is-default')
                                    </label>
                                </div>
                            </x-slot>

                            <!-- Form Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-shop::button
                                    button-type="button"
                                    class="primary-button"
                                    :title="trans('b2b_suite::app.shop.customers.account.requisitions.edit.btn-save')"
                                />
                            </x-slot>
                        </x-shop::modal>
                    </form>
                </x-shop::form>
                
                <!-- Requisition Items Shimmer Effect -->
                <template v-if="!isLoading">
                    <x-shop::shimmer.checkout.cart :count="3" />
                </template>

                <!-- Requisition Items Information -->
                <template v-else>
                    <div
                        class="mt-8 flex flex-wrap gap-20 pb-8 max-1060:flex-col max-md:mt-0 max-md:gap-[30px] max-md:pb-0"
                        v-if="requisitionItems?.length"
                    >
                        <div class="flex flex-1 flex-col gap-6 max-md:gap-5">

                            {!! view_render_event('bagisto.shop.customers.account.requisition.mass_actions.before') !!}

                            <!-- Item Mass Action Container -->
                            <div class="flex items-center justify-between border-b border-zinc-200 pb-2.5 max-md:py-2.5">
                                <div class="flex select-none items-center">
                                    <input
                                        type="checkbox"
                                        id="select-all"
                                        class="peer hidden"
                                        v-model="allSelected"
                                        @change="selectAll"
                                    >

                                    <label
                                        class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                        for="select-all"
                                        tabindex="0"
                                        aria-label="@lang('b2b_suite::app.shop.customers.account.requisitions.select-all')"
                                        aria-labelledby="select-all-label"
                                    >
                                    </label>

                                    <span
                                        class="text-xl max-sm:text-sm ltr:ml-2.5 rtl:mr-2.5"
                                        role="heading"
                                        aria-level="2"
                                    >
                                        @{{ "@lang('b2b_suite::app.shop.customers.account.requisitions.items-selected')".replace(':count', selectedItemsCount) }}
                                    </span>
                                </div>

                                <div v-if="selectedItemsCount">
                                    <span
                                        class="cursor-pointer text-base text-blue-700 max-sm:text-xs"
                                        role="button"
                                        tabindex="0"
                                        @click="removeItem(null)"
                                    >
                                        @lang('b2b_suite::app.shop.customers.account.requisitions.remove-selected')
                                    </span>
                                </div>
                            </div>

                            {!! view_render_event('bagisto.shop.customers.account.requisition.mass_actions.after') !!}

                            {!! view_render_event('bagisto.shop.customers.account.requisition.item.listing.before') !!}

                            <!-- Item Item Listing Container -->
                            <div
                                class="grid gap-y-6"
                                v-for="item in requisitionItems"
                            >
                                <div class="flex justify-between gap-x-2.5 border-b border-zinc-200 pb-5">
                                    <div class="flex gap-x-5">
                                        <div class="mt-11 select-none max-md:mt-9 max-sm:mt-7">
                                            <input
                                                type="checkbox"
                                                :id="'item_' + item.id"
                                                class="peer hidden"
                                                v-model="item.selected"
                                                @change="updateAllSelected"
                                            >

                                            <label
                                                class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                                :for="'item_' + item.id"
                                                tabindex="0"
                                                aria-label="@lang('b2b_suite::app.shop.customers.account.requisitions.select-cart-item')"
                                                aria-labelledby="select-item-label"
                                            ></label>
                                        </div>

                                        {!! view_render_event('bagisto.shop.customers.account.requisition.item_image.before') !!}

                                        <!-- Item Image -->
                                        <a :href="`{{ route('shop.product_or_category.index', '') }}/${item.product_url_key}`">
                                            <x-shop::media.images.lazy
                                                class="h-[110px] max-w-[110px] rounded-xl max-md:h-20 max-md:max-w-20"
                                                ::src="item.base_image.small_image_url"
                                                ::alt="item.name"
                                                ::alt="item.name"
                                                width="110"
                                                height="110"
                                                ::key="item.id"
                                                ::index="item.id"
                                            />
                                        </a>

                                        {!! view_render_event('bagisto.shop.customers.account.requisition.item_image.after') !!}

                                        <!-- Item Options Container -->
                                        <div class="grid place-content-start gap-y-2.5 max-md:gap-y-0">
                                            {!! view_render_event('bagisto.shop.customers.account.requisition.item_name.before') !!}

                                            <a :href="`{{ route('shop.product_or_category.index', '') }}/${item.product_url_key}`">
                                                <p class="text-base font-medium max-sm:text-sm">
                                                    @{{ item.name }}
                                                </p>
                                            </a>

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.item_name.after') !!}

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.item_details.before') !!}

                                            <!-- Item Options Container -->
                                            <div 
                                                class="grid select-none gap-x-2.5 gap-y-1.5"
                                                v-if="Object.keys(item.options).length"
                                            >
                                                <!-- Details Toggler -->
                                                <div class="">
                                                    <p
                                                        class="flex cursor-pointer items-center gap-x-4 text-base max-md:gap-x-1.5 max-sm:text-xs"
                                                        @click="item.option_show = ! item.option_show"
                                                    >
                                                        @lang('b2b_suite::app.shop.customers.account.requisitions.see-details')

                                                        <span
                                                            class="text-2xl max-md:text-lg"
                                                            :class="{'icon-arrow-up': item.option_show, 'icon-arrow-down': ! item.option_show}"
                                                        ></span>
                                                    </p>
                                                </div>

                                                <!-- Option Details -->
                                                <div
                                                    class="grid gap-2"
                                                    v-show="item.option_show"
                                                >
                                                    <template v-for="attribute in item.options">
                                                        <div class="max-md:grid max-md:gap-0.5">
                                                            <p class="text-sm font-medium text-zinc-500 max-md:font-normal max-sm:text-xs">
                                                                @{{ attribute.attribute_name + ':' }}
                                                            </p>

                                                            <p class="text-sm max-sm:text-xs">
                                                                <template v-if="attribute?.attribute_type === 'file'">
                                                                    <a
                                                                        :href="attribute.file_url"
                                                                        class="text-blue-700"
                                                                        target="_blank"
                                                                        :download="attribute.file_name"
                                                                    >
                                                                        @{{ attribute.file_name }}
                                                                    </a>
                                                                </template>

                                                                <template v-else>
                                                                    @{{ attribute.option_label }}
                                                                </template>
                                                            </p>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.item_details.after') !!}

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.formatted_total.before') !!}

                                            <div class="md:hidden">
                                                <p class="text-lg font-semibold max-md:text-sm">
                                                    @{{ item.formatted_total }}
                                                </p>

                                                <span
                                                    class="cursor-pointer text-base text-blue-700 max-md:hidden"
                                                    role="button"
                                                    tabindex="0"
                                                    @click="removeItem(item.id)"
                                                >
                                                    @lang('b2b_suite::app.shop.customers.account.requisitions.remove')
                                                </span>
                                            </div>

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.formatted_total.after') !!}

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.quantity_changer.before') !!}

                                            <div class="flex items-center gap-2.5 max-md:mt-2.5">
                                                <x-shop::quantity-changer
                                                    class="flex max-w-max items-center gap-x-2.5 rounded-[54px] border border-navyBlue px-3.5 py-1.5 max-md:gap-x-1.5 max-md:px-1 max-md:py-0.5"
                                                    name="quantity"
                                                    ::value="item?.quantity"
                                                    @change="setItemQuantity(item.id, $event)"
                                                />

                                                <!-- For Mobile view Remove Button -->
                                                <span
                                                    class="hidden cursor-pointer text-sm text-blue-700 max-md:block"
                                                    role="button"
                                                    tabindex="0"
                                                    @click="removeItem(item.id)"
                                                >
                                                    @lang('b2b_suite::app.shop.customers.account.requisitions.remove')
                                                </span>
                                            </div>

                                            {!! view_render_event('bagisto.shop.customers.account.requisition.quantity_changer.after') !!}
                                        </div>
                                    </div>

                                    <div class="text-right max-md:hidden">
                                        {!! view_render_event('bagisto.shop.customers.account.requisition.total.before') !!}
                                        
                                        <p class="text-lg font-semibold">
                                            @{{ item.formatted_total }}
                                        </p>

                                        {!! view_render_event('bagisto.shop.customers.account.requisition.total.after') !!}

                                        {!! view_render_event('bagisto.shop.customers.account.requisition.remove_button.before') !!}

                                        <!-- Item Remove Button -->
                                        <span
                                            class="cursor-pointer text-base text-blue-700"
                                            role="button"
                                            tabindex="0"
                                            @click="removeItem(item.id)"
                                        >
                                            @lang('b2b_suite::app.shop.customers.account.requisitions.remove')
                                        </span>

                                        {!! view_render_event('bagisto.shop.customers.account.requisition.remove_button.after') !!}
                                    </div>
                                </div>
                            </div>

                            {!! view_render_event('bagisto.shop.customers.account.requisition.controls.before') !!}

                            <!-- Requisition Item Actions -->
                            <div class="flex flex-wrap justify-end gap-8 max-md:justify-between max-md:gap-5">

                                {!! view_render_event('bagisto.shop.customers.account.requisition.move_to_cart.before') !!}

                                <x-shop::button
                                    v-if="selectedItemsCount"
                                    class="secondary-button max-h-14 rounded-2xl max-md:rounded-lg max-md:px-6 max-md:py-3 max-md:text-sm max-sm:py-2"
                                    :title="trans('b2b_suite::app.shop.customers.account.requisitions.move-to-cart')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring"
                                    @click="moveToCartSelectedItems()"
                                />

                                {!! view_render_event('bagisto.shop.customers.account.requisition.move_to_cart.after') !!}

                                {!! view_render_event('bagisto.shop.customers.account.requisition.update_item.before') !!}

                                <x-shop::button
                                    class="secondary-button max-h-14 rounded-2xl max-md:rounded-lg max-md:px-6 max-md:py-3 max-md:text-sm max-sm:py-2"
                                    :title="trans('b2b_suite::app.shop.customers.account.requisitions.update-items')"
                                    ::loading="isStoring"
                                    ::disabled="isStoring"
                                    @click="updateItems()"
                                />

                                {!! view_render_event('bagisto.shop.customers.account.requisition.update_item.after') !!}
                            </div>

                            {!! view_render_event('bagisto.shop.customers.account.requisition.controls.after') !!}
                        </div>
                    </div>

                    <!-- Empty Requisition Item Section -->
                    <div
                        class="m-auto grid w-full place-content-center items-center justify-items-center py-32 text-center"
                        v-else
                    >
                        <img
                            class="max-md:h-[100px] max-md:w-[100px]"
                            src="{{ bagisto_asset('images/thank-you.png') }}"
                            alt="@lang('b2b_suite::app.shop.customers.account.requisitions.empty-message')"
                        />

                        <p
                            class="text-xl max-md:text-sm"
                            role="heading"
                        >
                            @lang('b2b_suite::app.shop.customers.account.requisitions.empty-message')
                        </p>
                    </div>
                </template>
            </div>
        </script>

        <script type="module">
            app.component("v-requisition-lists", {
                template: '#v-requisition-lists-template',

                data() {
                    return  {
                        allSelected: false,

                        applied: {
                            quantity: {},
                        },

                        requisitionItems: [],

                        isLoading: true,

                        requisition: @json($requisition),
                    }
                },

                computed: {
                    selectedItemsCount() {
                        return this.requisitionItems.filter(item => item.selected).length;
                    },
                },
                
                created() {
                    this.loadItems();
                },

                methods: {
                    loadItems() {
                        this.isLoading = false;
                        this.$axios.get("{{ route('shop.customers.account.requisitions.items') }}", {
                                params: {id: this.requisition.id}
                            })
                            .then(response => {
                                this.isLoading = true;
                                this.requisitionItems = response.data.data;
                            })
                            .catch(error => {
                                console.error("Error loading requisition items:", error);
                            });
                    },
                    
                    selectAll() {
                        for (let item of this.requisitionItems) {
                            item.selected = this.allSelected;
                        }
                    },

                    updateAllSelected() {
                        this.allSelected = this.requisitionItems.every(item => item.selected);
                    },

                    setItemQuantity(itemId, quantity) {
                        this.applied.quantity[itemId] = quantity;
                    },
                    
                    removeItem(itemId) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.requisitionItems.flatMap(item => item.selected ? item.id : []);
                                
                                this.$axios.post('{{ route('shop.customers.account.requisitions.delete_items') }}', {
                                        '_method': 'DELETE',
                                        'requisition_id': this.requisition.id,
                                        'requisition_item_ids': selectedItemsIds.length ? selectedItemsIds : [itemId],
                                    })
                                    .then(response => {
                                        this.requisitionItems = response.data.data;

                                        this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });

                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    updateItems() {
                        this.isStoring = true;

                        this.$axios.put('{{ route('shop.customers.account.requisitions.update_items') }}', { 
                            requisition_id: this.requisition.id,
                            qty: this.applied.quantity
                        })
                            .then(response => {
                                this.requisitionItems = response.data.data;

                                if (response.data.message) {
                                    this.$emitter.emit('add-flash', { type: 'success', message: response.data.message });
                                } else {
                                    this.$emitter.emit('add-flash', { type: 'warning', message: response.data.data.message });
                                }

                                this.isStoring = false;

                            })
                            .catch(error => {
                                this.isStoring = false;
                            });
                    },
                    
                    updateRequisition(params, { resetForm, setErrors  }) {
                        this.isLoading = true;

                        let formData = new FormData(this.$refs.updateRequisitionForm);

                        if (params.requisition_id) {
                            formData.append('_method', 'put');
                        }

                        this.$axios.post("{{ route('shop.customers.account.requisitions.update', $requisition->id) }}", formData)
                        .then((response) => {
                            this.isLoading = false;
                            
                            if (response.data.data) {
                                this.requisition = response.data.data;
                            }

                            this.$refs.updateRequisitionModal.close();
                            
                            if (response.data.redirect_url) {
                                // window.location.href = response.data.redirect_url;
                            }

                            resetForm();
                        })
                        .catch(error => {
                            this.isLoading = false;

                            if (error.response.status == 422) {
                                setErrors(error.response.data.errors);
                            }
                        });
                    },

                    moveToCartSelectedItems() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                const selectedItemsIds = this.requisitionItems.flatMap(item => item.selected ? item.id : []);

                                this.$axios.post('{{ route('shop.customers.account.requisitions.move_to_cart') }}', {
                                        'requisition_id': this.requisition.id,
                                        'ids': selectedItemsIds,
                                    })
                                    .then(response => {
                                        if (response.data.redirect_url) {
                                            window.location.href = response.data.redirect_url;
                                        }
                                    })
                                    .catch(error => {});
                            }
                        });
                    },

                    resetForm() {
                        this.requisition = {
                            image: [],
                        };
                    },
                }
            });
        </script>
    @endPushOnce
</x-shop::layouts.account>