<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.requisitions.edit.title' , ['id' => $requisition->id])
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="requisitions.edit" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="flex-auto max-md:px-4">
        {!! view_render_event('bagisto.shop.customers.account.requisition.edit.before', ['requisition' => $requisition]) !!}

        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-2.5">
                <!-- Back Button (mobile) -->
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.requisitions.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base">
                    @lang('b2b::app.shop.customers.account.requisitions.edit.title' , ['id' => $requisition->id])
                </h2>
            </div>

            <!-- Back Button (desktop) -->
            <a
                href="{{ route('shop.customers.account.requisitions.index') }}"
                class="transparent-button px-5 py-2.5 hover:bg-gray-100 max-md:hidden"
            >
                @lang('b2b::app.shop.customers.account.requisitions.edit.btn-back')
            </a>
        </div>

        <div class="mt-6 max-md:mt-4">
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
            <div class="b2b-requisition-detail grid gap-6 max-md:gap-4">
                <!-- List Information Card -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 max-md:p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="flex items-start gap-4 max-sm:gap-3">
                            <span class="icon-cart grid h-12 w-12 shrink-0 place-items-center rounded-full bg-blue-50 text-2xl text-blue-600 max-sm:h-10 max-sm:w-10 max-sm:text-xl"></span>

                            <div class="grid gap-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2
                                        class="text-xl font-semibold text-gray-900 max-sm:text-base"
                                        v-text="requisition.name"
                                    ></h2>

                                    <!-- Default Label -->
                                    <span
                                        v-if="requisition.is_default"
                                        class="rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700"
                                    >
                                        @lang('b2b::app.shop.customers.account.requisitions.edit.default-label')
                                    </span>
                                </div>

                                <p
                                    class="text-sm text-gray-500"
                                    v-text="requisition.description"
                                ></p>
                            </div>
                        </div>

                        <!-- Rename Requisition -->
                        <button
                            type="button"
                            class="flex items-center gap-1.5 rounded-lg border border-zinc-200 px-3.5 py-2 text-sm font-medium text-gray-700 transition-all hover:border-zinc-300 hover:bg-gray-50 hover:text-gray-900"
                            @click="$refs.updateRequisitionModal.open()"
                        >
                            <span class="icon-edit text-lg"></span>

                            @lang('b2b::app.shop.customers.account.requisitions.edit.link-rename')
                        </button>
                    </div>
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
                                <p class="text-lg font-bold text-gray-800">
                                    @lang('b2b::app.shop.customers.account.requisitions.edit.edit-title')
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
                                        @lang('b2b::app.shop.customers.account.requisitions.edit.name')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        name="name"
                                        rules="required"
                                        ::value="requisition.name"
                                        :label="trans('b2b::app.shop.customers.account.requisitions.edit.name')"
                                    />

                                    <x-shop::form.control-group.error control-name="name" />
                                </x-shop::form.control-group>

                                <x-shop::form.control-group>
                                    <x-shop::form.control-group.label class="required">
                                        @lang('b2b::app.shop.customers.account.requisitions.edit.description')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="textarea"
                                        name="description"
                                        rows="4"
                                        rules="required"
                                        ::value="requisition.description"
                                        :label="trans('b2b::app.shop.customers.account.requisitions.edit.description')"
                                        :placeholder="trans('b2b::app.shop.customers.account.requisitions.edit.description')"
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
                                        @lang('b2b::app.shop.customers.account.requisitions.edit.is-default')
                                    </label>
                                </div>
                            </x-slot>

                            <!-- Form Modal Footer -->
                            <x-slot:footer>
                                <!-- Save Button -->
                                <x-shop::button
                                    button-type="button"
                                    class="primary-button"
                                    :title="trans('b2b::app.shop.customers.account.requisitions.edit.btn-save')"
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
                    <template v-if="requisitionItems?.length">
                        {!! view_render_event('bagisto.shop.customers.account.requisition.item.listing.before') !!}

                        <!-- Items Card -->
                        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                            {!! view_render_event('bagisto.shop.customers.account.requisition.mass_actions.before') !!}

                            <!-- Item Mass Action Header -->
                            <div class="flex items-center justify-between gap-2.5 border-b border-zinc-200 px-6 py-4 max-md:px-4">
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
                                        aria-label="@lang('b2b::app.shop.customers.account.requisitions.select-all')"
                                        aria-labelledby="select-all-label"
                                    ></label>

                                    <span
                                        class="text-base font-medium text-gray-700 max-sm:text-sm ltr:ml-2.5 rtl:mr-2.5"
                                        role="heading"
                                        aria-level="2"
                                    >
                                        @{{ "@lang('b2b::app.shop.customers.account.requisitions.items-selected')".replace(':count', selectedItemsCount) }}
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    v-if="selectedItemsCount"
                                    class="group flex items-center gap-1 text-sm font-medium text-red-600 transition-all max-sm:text-xs"
                                    @click="removeItem(null)"
                                >
                                    <span class="icon-bin text-base"></span>

                                    <span class="group-hover:underline">
                                        @lang('b2b::app.shop.customers.account.requisitions.remove-selected')
                                    </span>
                                </button>
                            </div>

                            {!! view_render_event('bagisto.shop.customers.account.requisition.mass_actions.after') !!}

                            <!-- Item Listing -->
                            <div
                                class="flex gap-x-5 border-b border-zinc-100 px-6 py-5 transition-all last:border-b-0 hover:bg-gray-50/60 max-md:gap-x-3 max-md:px-4"
                                v-for="item in requisitionItems"
                            >
                                <!-- Selection Checkbox -->
                                <div class="select-none pt-1">
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
                                        aria-label="@lang('b2b::app.shop.customers.account.requisitions.select-cart-item')"
                                        aria-labelledby="select-item-label"
                                    ></label>
                                </div>

                                {!! view_render_event('bagisto.shop.customers.account.requisition.item_image.before') !!}

                                <!-- Item Image -->
                                <a
                                    class="shrink-0"
                                    :href="'{{ route('shop.product_or_category.index', '__SLUG__') }}'.replace('__SLUG__', item.product_url_key)"
                                >
                                    <x-shop::media.images.lazy
                                        class="h-24 w-24 rounded-lg border border-zinc-100 max-md:h-20 max-md:w-20"
                                        ::src="item.base_image.small_image_url"
                                        ::alt="item.name"
                                        width="96"
                                        height="96"
                                        ::key="item.id"
                                        ::index="item.id"
                                    />
                                </a>

                                {!! view_render_event('bagisto.shop.customers.account.requisition.item_image.after') !!}

                                <!-- Item Details -->
                                <div class="flex flex-1 flex-col gap-2">
                                    {!! view_render_event('bagisto.shop.customers.account.requisition.item_name.before') !!}

                                    <a :href="'{{ route('shop.product_or_category.index', '__SLUG__') }}'.replace('__SLUG__', item.product_url_key)">
                                        <p class="text-base font-medium text-gray-900 transition-all hover:text-blue-600 max-sm:text-sm">
                                            @{{ item.name }}
                                        </p>
                                    </a>

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.item_name.after') !!}

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.item_details.before') !!}

                                    <!-- Item Options -->
                                    <div
                                        class="grid select-none gap-x-2.5 gap-y-1.5"
                                        v-if="Object.keys(item.options).length"
                                    >
                                        <!-- Details Toggler -->
                                        <div>
                                            <p
                                                class="flex w-max cursor-pointer items-center gap-x-2 text-sm text-blue-600 max-sm:text-xs"
                                                @click="item.option_show = ! item.option_show"
                                            >
                                                @lang('b2b::app.shop.customers.account.requisitions.see-details')

                                                <span
                                                    class="text-lg"
                                                    :class="{'icon-arrow-up': item.option_show, 'icon-arrow-down': ! item.option_show}"
                                                ></span>
                                            </p>
                                        </div>

                                        <!-- Option Details -->
                                        <div
                                            class="grid gap-2 rounded-lg bg-gray-50 p-3"
                                            v-show="item.option_show"
                                        >
                                            <template v-for="attribute in item.options">
                                                <div class="flex flex-wrap gap-x-1.5">
                                                    <p class="text-sm font-medium text-zinc-500 max-sm:text-xs">
                                                        @{{ attribute.attribute_name + ':' }}
                                                    </p>

                                                    <p class="text-sm text-gray-700 max-sm:text-xs">
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

                                    <!-- Mobile Total -->
                                    <p class="text-base font-semibold text-gray-900 md:hidden">
                                        @{{ item.formatted_total }}
                                    </p>

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.formatted_total.after') !!}

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.quantity_changer.before') !!}

                                    <!-- Quantity Changer -->
                                    <div class="mt-1 flex items-center gap-3">
                                        <x-shop::quantity-changer
                                            class="flex max-w-max items-center gap-x-2.5 rounded-lg border border-zinc-300 px-3 py-1.5 max-md:gap-x-1.5 max-md:px-2 max-md:py-1"
                                            name="quantity"
                                            ::value="item?.quantity"
                                            @change="setItemQuantity(item.id, $event)"
                                        />

                                        <!-- Mobile Remove Button -->
                                        <button
                                            type="button"
                                            class="group flex items-center gap-1 text-sm font-medium text-red-600 transition-all md:hidden"
                                            @click="removeItem(item.id)"
                                        >
                                            <span class="icon-bin text-base"></span>

                                            <span class="group-hover:underline">
                                                @lang('b2b::app.shop.customers.account.requisitions.remove')
                                            </span>
                                        </button>
                                    </div>

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.quantity_changer.after') !!}
                                </div>

                                <!-- Desktop Total & Remove -->
                                <div class="grid content-start justify-items-end gap-2 ltr:text-right rtl:text-left max-md:hidden">
                                    {!! view_render_event('bagisto.shop.customers.account.requisition.total.before') !!}

                                    <p class="text-base font-semibold text-gray-900">
                                        @{{ item.formatted_total }}
                                    </p>

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.total.after') !!}

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.remove_button.before') !!}

                                    <!-- Item Remove Button -->
                                    <button
                                        type="button"
                                        class="group flex items-center gap-1 text-sm font-medium text-red-600 transition-all"
                                        @click="removeItem(item.id)"
                                    >
                                        <span class="icon-bin text-base"></span>

                                        <span class="group-hover:underline">
                                            @lang('b2b::app.shop.customers.account.requisitions.remove')
                                        </span>
                                    </button>

                                    {!! view_render_event('bagisto.shop.customers.account.requisition.remove_button.after') !!}
                                </div>
                            </div>
                        </div>

                        {!! view_render_event('bagisto.shop.customers.account.requisition.item.listing.after') !!}

                        {!! view_render_event('bagisto.shop.customers.account.requisition.controls.before') !!}

                        <!-- Requisition Item Actions -->
                        <div class="flex flex-wrap justify-end gap-3 max-md:justify-between">
                            {!! view_render_event('bagisto.shop.customers.account.requisition.move_to_cart.before') !!}

                            <x-shop::button
                                v-if="selectedItemsCount"
                                class="secondary-button rounded-lg px-6 py-3 max-md:text-sm"
                                :title="trans('b2b::app.shop.customers.account.requisitions.move-to-cart')"
                                ::loading="isStoring"
                                ::disabled="isStoring"
                                @click="moveToCartSelectedItems()"
                            />

                            {!! view_render_event('bagisto.shop.customers.account.requisition.move_to_cart.after') !!}

                            {!! view_render_event('bagisto.shop.customers.account.requisition.update_item.before') !!}

                            <x-shop::button
                                class="primary-button rounded-lg px-6 py-3 max-md:text-sm"
                                :title="trans('b2b::app.shop.customers.account.requisitions.update-items')"
                                ::loading="isStoring"
                                ::disabled="isStoring"
                                @click="updateItems()"
                            />

                            {!! view_render_event('bagisto.shop.customers.account.requisition.update_item.after') !!}
                        </div>

                        {!! view_render_event('bagisto.shop.customers.account.requisition.controls.after') !!}
                    </template>

                    <!-- Empty Requisition Item Section -->
                    <div
                        class="flex flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-zinc-200 bg-white py-20 text-center max-md:py-14"
                        v-else
                    >
                        <img
                            class="h-24 w-24 opacity-90 max-md:h-20 max-md:w-20"
                            src="{{ bagisto_asset('images/thank-you.png') }}"
                            alt="@lang('b2b::app.shop.customers.account.requisitions.empty-message')"
                        />

                        <p
                            class="text-lg font-medium text-gray-700 max-md:text-base"
                            role="heading"
                        >
                            @lang('b2b::app.shop.customers.account.requisitions.empty-message')
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

                        isStoring: false,

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

                                        this.allSelected = false;

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
                                window.location.href = response.data.redirect_url;
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
