@php
    $catalog ??= null;
    $products ??= collect();
    $companies ??= collect();
    $mode ??= 'create';

    $normalizeLeaf = fn ($l) => [
        'id'              => $l['id'],
        'sku'             => $l['sku'],
        'name'            => $l['name'],
        'price'           => (float) ($l['price'] ?? 0),
        'formatted_price' => $l['formatted_price'],
        'price_type'      => $l['price_type'] ?? 'fixed',
        'price_value'     => ($l['price_value'] ?? '') === '' ? '' : (float) $l['price_value'],
    ];

    $initialProducts = collect($products)->map(fn ($p) => [
        'id'           => $p['id'],
        'sku'          => $p['sku'],
        'name'         => $p['name'],
        'type'         => $p['type'] ?? 'simple',
        'image'        => $p['image'] ?? null,
        'priceable'    => (bool) ($p['priceable'] ?? true),
        'is_composite' => (bool) ($p['is_composite'] ?? false),
        'expanded'     => false,
        'selected'     => false,
        'leaves'       => collect($p['leaves'] ?? [])->map($normalizeLeaf)->values(),
    ])->values();

    $initialCompanies = collect($companies)->map(fn ($c) => [
        'id'    => $c['id'],
        'name'  => $c['name'],
        'email' => $c['email'],
    ])->values();

    $currencySymbol = core()->currencySymbol(core()->getBaseCurrencyCode());

    $productTypes = collect(config('product_types'))
        ->mapWithKeys(fn ($type, $code) => [$code => trans($type['name'])])
        ->all();
@endphp

@push('styles')
    <style>
        /* Widen only the Assign Products modal (scoped via its marker), without touching the core modal component. */
        .box-shadow:has(.b2b-assign-modal) {
            max-width: 64rem !important;
        }

        /* Skeleton shimmer for the loading state. */
        .b2b-shimmer {
            border-radius: 0.25rem;
            background-image: linear-gradient(90deg, rgba(0, 0, 0, 0.06) 25%, rgba(0, 0, 0, 0.12) 37%, rgba(0, 0, 0, 0.06) 63%);
            background-size: 400% 100%;
            animation: b2b-shimmer 1.4s ease infinite;
        }

        .dark .b2b-shimmer {
            background-image: linear-gradient(90deg, rgba(255, 255, 255, 0.07) 25%, rgba(255, 255, 255, 0.14) 37%, rgba(255, 255, 255, 0.07) 63%);
        }

        @keyframes b2b-shimmer {
            0% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
@endpush

<x-admin::form
    :action="$action"
    :method="$method"
>
    <!-- Action Buttons -->
    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.company-catalogs.'.$mode.'.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.b2b.company_catalogs.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('b2b::app.admin.company-catalogs.'.$mode.'.back-btn')
            </a>

            <button
                type="submit"
                class="primary-button"
            >
                @lang('b2b::app.admin.company-catalogs.'.$mode.'.save-btn')
            </button>
        </div>
    </div>

    <v-company-catalog></v-company-catalog>
</x-admin::form>

@pushOnce('scripts')
    <script type="text/x-template" id="v-company-catalog-template">
        <div>
        <!-- Main Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Column -->
            <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                <!-- Products -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.company-catalogs.products')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <!-- Info + Assign Products (right) -->
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-sm text-gray-500 dark:text-gray-300">
                                @lang('b2b::app.admin.company-catalogs.products-info')
                            </p>

                            <button
                                type="button"
                                class="secondary-button flex shrink-0 items-center gap-1.5 !rounded-lg"
                                @click="openAssignModal"
                            >
                                <span class="icon-plus text-lg"></span>

                                @lang('b2b::app.admin.company-catalogs.assign-products')
                            </button>
                        </div>

                        <!-- Mass Action Bar (shown only when products are selected) -->
                        <div
                            v-if="selectedCount"
                            class="mt-4 flex flex-wrap items-center gap-2.5 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950"
                        >
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                @{{ "@lang('b2b::app.admin.company-catalogs.items-selected')".replace(':count', selectedCount) }}
                            </span>

                            <select
                                v-model="massAction"
                                class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="update-price">@lang('b2b::app.admin.company-catalogs.update-price')</option>
                                <option value="delete">@lang('b2b::app.admin.company-catalogs.delete-action')</option>
                            </select>

                            <template v-if="massAction === 'update-price'">
                                <select
                                    v-model="massType"
                                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                    <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                </select>

                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    v-model="massValue"
                                    class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    :placeholder="massType === 'discount' ? '%' : '@lang('b2b::app.admin.company-catalogs.value')'"
                                >
                            </template>

                            <button
                                v-if="massAction"
                                type="button"
                                class="secondary-button !rounded-lg !px-4 !py-1.5 text-sm"
                                @click="runMassAction"
                            >
                                @lang('b2b::app.admin.company-catalogs.apply')
                            </button>
                        </div>

                        <!-- Selected products -->
                        <div class="mt-3 overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                            <table class="w-full" style="table-layout: fixed;">
                                <colgroup>
                                    <col style="width: 3rem;">
                                    <col>
                                    <col style="width: 9rem;">
                                    <col style="width: 9rem;">
                                    <col style="width: 8rem;">
                                    <col style="width: 9rem;">
                                    <col style="width: 3.5rem;">
                                </colgroup>

                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                        <th class="px-3 py-4">
                                            <input
                                                type="checkbox"
                                                class="cursor-pointer"
                                                :checked="allSelected"
                                                @change="toggleAll($event)"
                                            >
                                        </th>
                                        <th class="px-4 py-4">
                                            <button type="button" class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white" @click="sortBy('name')">
                                                @lang('b2b::app.admin.company-catalogs.product')
                                                <span :class="sortIcon('name')" class="text-base"></span>
                                            </button>
                                        </th>
                                        <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.base-price')</th>
                                        <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.price-type')</th>
                                        <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.value')</th>
                                        <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.new-price')</th>
                                        <th class="px-4 py-4"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-if="! products.length">
                                        <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                            @lang('b2b::app.admin.company-catalogs.no-products')
                                        </td>
                                    </tr>

                                    <template
                                        v-for="product in paginatedProducts"
                                        :key="product.id"
                                    >
                                        <!-- Single-leaf product (simple / virtual / downloadable): price edited inline -->
                                        <tr
                                            v-if="product.priceable && ! product.is_composite"
                                            class="border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                        >
                                            <td class="px-3 py-3">
                                                <input type="checkbox" class="cursor-pointer" v-model="product.selected">
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    <img class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800" :src="product.image">

                                                    <div class="grid">
                                                        <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                        <span class="text-xs text-gray-500">@{{ product.sku }}</span>

                                                        <span
                                                            v-if="isSharedLeaf(product.leaves[0].id)"
                                                            class="mt-1 flex items-start gap-1 text-xs text-gray-500 dark:text-gray-400"
                                                        >
                                                            <span class="icon-information shrink-0 text-base text-blue-500"></span>
                                                            <span>@lang('b2b::app.admin.company-catalogs.shared-price-note')</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">@{{ product.leaves[0].formatted_price }}</td>

                                            <td class="px-4 py-3">
                                                <select class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" v-model="product.leaves[0].price_type">
                                                    <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                                    <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                                </select>
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="relative w-28">
                                                    <input
                                                        type="number" step="0.01" min="0"
                                                        class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                        :class="product.leaves[0].price_type === 'discount' ? 'ltr:pr-7 rtl:pl-7' : ''"
                                                        v-model="product.leaves[0].price_value"
                                                        :placeholder="product.leaves[0].price_type === 'discount' ? '0' : '@lang('b2b::app.admin.company-catalogs.price-placeholder')'"
                                                    >
                                                    <span v-if="product.leaves[0].price_type === 'discount'" class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                                </div>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white">@{{ newPrice(product.leaves[0]) }}</td>

                                            <td class="px-4 py-3 text-right">
                                                <span class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                            </td>
                                        </tr>

                                        <!-- Booking: visibility only (core never reads group prices for booking) -->
                                        <tr
                                            v-else-if="! product.priceable"
                                            class="border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                        >
                                            <td class="px-3 py-3">
                                                <input type="checkbox" class="cursor-pointer" v-model="product.selected">
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    <img class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800" :src="product.image">

                                                    <div class="grid">
                                                        <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                        <span class="text-xs text-gray-500">@{{ product.sku }} · @{{ product.type }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td colspan="4" class="px-4 py-3 text-sm italic text-gray-400">
                                                @lang('b2b::app.admin.company-catalogs.visibility-only')
                                            </td>

                                            <td class="px-4 py-3 text-right">
                                                <span class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                            </td>
                                        </tr>

                                        <!-- Composite (configurable / grouped / bundle): header row + expandable leaf prices -->
                                        <template v-else>
                                            <tr class="border-t border-gray-100 bg-gray-50 transition-all dark:border-gray-800 dark:bg-gray-950">
                                                <td class="px-3 py-3">
                                                    <input type="checkbox" class="cursor-pointer" v-model="product.selected">
                                                </td>

                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2.5">
                                                        <button type="button" class="grid h-6 w-6 shrink-0 place-items-center rounded text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800" @click="product.expanded = ! product.expanded">
                                                            <span :class="product.expanded ? 'icon-arrow-up' : 'icon-arrow-down'" class="text-xl"></span>
                                                        </button>

                                                        <img class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800" :src="product.image">

                                                        <div class="grid">
                                                            <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                            <span class="text-xs text-gray-500">@{{ product.sku }} · @{{ product.type }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td colspan="4" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                    <span class="cursor-pointer hover:underline" @click="product.expanded = ! product.expanded">
                                                        @{{ "@lang('b2b::app.admin.company-catalogs.leaf-count')".replace(':count', product.leaves.length) }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-3 text-right">
                                                    <span class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                                </td>
                                            </tr>

                                            <tr
                                                v-show="product.expanded"
                                                v-for="leaf in product.leaves"
                                                :key="leaf.id"
                                                class="border-t border-gray-100 dark:border-gray-800"
                                            >
                                                <td class="px-3 py-2.5"></td>

                                                <td class="px-4 py-2.5">
                                                    <div class="grid ps-8">
                                                        <span class="text-sm text-gray-700 dark:text-gray-200">@{{ leaf.name }}</span>
                                                        <span class="text-xs text-gray-500">@{{ leaf.sku }}</span>

                                                        <span
                                                            v-if="isSharedLeaf(leaf.id)"
                                                            class="mt-1 flex items-start gap-1 text-xs text-gray-500 dark:text-gray-400"
                                                        >
                                                            <span class="icon-information shrink-0 text-base text-blue-500"></span>
                                                            <span>@lang('b2b::app.admin.company-catalogs.shared-price-note')</span>
                                                        </span>
                                                    </div>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300">@{{ leaf.formatted_price }}</td>

                                                <td class="px-4 py-2.5">
                                                    <select class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" v-model="leaf.price_type">
                                                        <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                                        <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                                    </select>
                                                </td>

                                                <td class="px-4 py-2.5">
                                                    <div class="relative w-28">
                                                        <input
                                                            type="number" step="0.01" min="0"
                                                            class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                            :class="leaf.price_type === 'discount' ? 'ltr:pr-7 rtl:pl-7' : ''"
                                                            v-model="leaf.price_value"
                                                            :placeholder="leaf.price_type === 'discount' ? '0' : '@lang('b2b::app.admin.company-catalogs.price-placeholder')'"
                                                        >
                                                        <span v-if="leaf.price_type === 'discount'" class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                                    </div>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-white">@{{ newPrice(leaf) }}</td>

                                                <td class="px-4 py-2.5"></td>
                                            </tr>
                                        </template>
                                    </template>

                                    <!-- Shimmer rows while newly assigned products are being fetched -->
                                    <template v-if="productsLoading">
                                        <tr
                                            v-for="n in addingCount"
                                            :key="'product-skeleton-' + n"
                                            class="border-t border-gray-100 dark:border-gray-800"
                                        >
                                            <td class="px-3 py-3"><div class="b2b-shimmer h-4 w-4"></div></td>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="b2b-shimmer h-10 w-10"></div>
                                                    <div class="b2b-shimmer h-3 w-40"></div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-16"></div></td>
                                            <td class="px-4 py-3"><div class="b2b-shimmer h-8 w-24"></div></td>
                                            <td class="px-4 py-3"><div class="b2b-shimmer h-8 w-28"></div></td>
                                            <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-16"></div></td>
                                            <td class="px-4 py-3"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div
                            v-if="totalPages > 1"
                            class="mt-3 flex items-center justify-between gap-2 text-sm"
                        >
                            <span class="text-gray-500 dark:text-gray-400">
                                @{{ products.length }} @lang('b2b::app.admin.company-catalogs.products')
                            </span>

                            <div class="flex items-center gap-1.5">
                                <button
                                    type="button"
                                    class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    :class="{ 'cursor-not-allowed opacity-50': currentPage === 1 }"
                                    :disabled="currentPage === 1"
                                    @click="currentPage--"
                                >
                                    <span class="icon-sort-left rtl:icon-sort-right text-xl"></span>
                                </button>

                                <span class="px-2 text-gray-600 dark:text-gray-300">@{{ currentPage }} / @{{ totalPages }}</span>

                                <button
                                    type="button"
                                    class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    :class="{ 'cursor-not-allowed opacity-50': currentPage === totalPages }"
                                    :disabled="currentPage === totalPages"
                                    @click="currentPage++"
                                >
                                    <span class="icon-sort-right rtl:icon-sort-left text-xl"></span>
                                </button>
                            </div>
                        </div>

                        <!--
                            All assigned products' fields are submitted here (not just the
                            visible page), so pagination never drops a product or its price.
                        -->
                        <template v-for="product in products" :key="'field-' + product.id">
                            <input type="hidden" name="products[]" :value="product.id">

                            <template v-for="leaf in product.leaves" :key="'leaf-field-' + leaf.id">
                                <input type="hidden" :name="`prices[${leaf.id}][type]`" :value="leaf.price_type">
                                <input type="hidden" :name="`prices[${leaf.id}][value]`" :value="leaf.price_value">
                            </template>
                        </template>

                    </x-slot>
                </x-admin::accordion>
            </div>

            <!-- Right Column -->
            <div class="flex w-[360px] max-w-full flex-col gap-2">
                <!-- General -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.company-catalogs.general')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('b2b::app.admin.company-catalogs.name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="name"
                                rules="required"
                                :value="old('name', $catalog?->name)"
                                :label="trans('b2b::app.admin.company-catalogs.name')"
                                :placeholder="trans('b2b::app.admin.company-catalogs.name')"
                            />

                            <x-admin::form.control-group.error control-name="name" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('b2b::app.admin.company-catalogs.description')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="textarea"
                                name="description"
                                rows="4"
                                :value="old('description', $catalog?->description)"
                                :label="trans('b2b::app.admin.company-catalogs.description')"
                                :placeholder="trans('b2b::app.admin.company-catalogs.description')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('b2b::app.admin.company-catalogs.status')
                            </x-admin::form.control-group.label>

                            <input type="hidden" name="status" value="0">

                            <x-admin::form.control-group.control
                                type="switch"
                                name="status"
                                value="1"
                                :checked="(bool) old('status', $catalog?->status ?? true)"
                                :label="trans('b2b::app.admin.company-catalogs.status')"
                            />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                <!-- Companies -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.company-catalogs.companies')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <p class="mb-1 text-sm text-gray-500 dark:text-gray-300">
                            @lang('b2b::app.admin.company-catalogs.companies-info')
                        </p>

                        <p class="mb-3 text-xs text-amber-600 dark:text-amber-400">
                            @lang('b2b::app.admin.company-catalogs.companies-single-note')
                        </p>

                        <!-- Search -->
                        <div class="relative">
                            <input
                                type="text"
                                class="w-full rounded-md border border-gray-200 px-4 py-2.5 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                placeholder="@lang('b2b::app.admin.company-catalogs.search-companies')"
                                v-model="companyQuery"
                                @input="searchCompanies"
                            />

                            <div
                                v-if="companyResults.length"
                                class="absolute z-10 mt-1 max-h-72 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900"
                            >
                                <div
                                    v-for="result in companyResults"
                                    :key="result.id"
                                    class="flex cursor-pointer flex-col border-b border-gray-100 p-2.5 last:border-b-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                    @click="addCompany(result)"
                                >
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ result.name }}</span>
                                    <span class="text-xs text-gray-500">@{{ result.email }}</span>

                                    <span
                                        v-if="result.current_catalog"
                                        class="mt-1 w-max rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400"
                                    >
                                        @{{ "@lang('b2b::app.admin.company-catalogs.in-catalog')".replace(':name', result.current_catalog) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Selected companies -->
                        <div class="mt-4 grid gap-2">
                            <p v-if="! companies.length" class="py-4 text-center text-sm text-gray-500">
                                @lang('b2b::app.admin.company-catalogs.no-companies')
                            </p>

                            <div
                                v-for="company in companies"
                                :key="company.id"
                                class="flex items-center justify-between gap-2 rounded-md border border-gray-200 p-2.5 dark:border-gray-800"
                            >
                                <div class="grid">
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ company.name }}</span>
                                    <span class="text-xs text-gray-500">@{{ company.email }}</span>
                                </div>

                                <span
                                    class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600"
                                    @click="removeCompany(company.id)"
                                ></span>

                                <input type="hidden" name="companies[]" :value="company.id">
                            </div>
                        </div>
                    </x-slot>
                </x-admin::accordion>
            </div>
        </div>
                        <!-- Assign Products Modal -->
                        <x-admin::modal ref="assignProductsModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @lang('b2b::app.admin.company-catalogs.assign-products')
                                </p>
                            </x-slot>

                            <x-slot:content>
                                <div class="b2b-assign-modal">
                                <!-- Search + type filter -->
                                <div class="mb-4 flex flex-wrap items-center gap-3">
                                    <div class="relative flex-1">
                                        <span class="icon-search pointer-events-none absolute top-2.5 text-xl text-gray-400 ltr:left-3 rtl:right-3"></span>

                                        <input
                                            type="text"
                                            class="w-full rounded-lg border border-gray-200 py-2.5 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white ltr:pl-10 rtl:pr-10"
                                            placeholder="@lang('b2b::app.admin.company-catalogs.search-products')"
                                            v-model="modalQuery"
                                            @input="searchModalProducts"
                                        >
                                    </div>

                                    <select
                                        v-model="modalType"
                                        @change="fetchModalProducts(1)"
                                        class="w-52 shrink-0 rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                                    >
                                        <option value="">@lang('b2b::app.admin.company-catalogs.all-types')</option>

                                        @foreach ($productTypes as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Product list (fixed height so the modal doesn't resize between pages) -->
                                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                                    <div class="h-96 overflow-y-auto">
                                        <table class="w-full" style="table-layout: fixed;">
                                            <colgroup>
                                                <col style="width: 3rem;">
                                                <col>
                                                <col style="width: 10rem;">
                                                <col style="width: 9rem;">
                                                <col style="width: 8rem;">
                                                <col style="width: 6rem;">
                                            </colgroup>

                                            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                                                <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                                    <th class="px-3 py-4">
                                                        <input
                                                            type="checkbox"
                                                            class="cursor-pointer"
                                                            :checked="modalAllChecked"
                                                            @change="toggleModalAll($event)"
                                                        >
                                                    </th>
                                                    <th class="px-4 py-4">
                                                        <button type="button" class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white" @click="sortModalBy('name')">
                                                            @lang('b2b::app.admin.company-catalogs.product')
                                                            <span :class="modalSortIcon('name')" class="text-base"></span>
                                                        </button>
                                                    </th>
                                                    <th class="px-4 py-4">
                                                        <button type="button" class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white" @click="sortModalBy('sku')">
                                                            @lang('b2b::app.admin.company-catalogs.sku')
                                                            <span :class="modalSortIcon('sku')" class="text-base"></span>
                                                        </button>
                                                    </th>
                                                    <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.type')</th>
                                                    <th class="px-4 py-4">@lang('b2b::app.admin.company-catalogs.base-price')</th>
                                                    <th class="px-4 py-4"></th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <template v-if="modalLoading">
                                                    <tr
                                                        v-for="n in 6"
                                                        :key="'skeleton-' + n"
                                                        class="border-t border-gray-100 dark:border-gray-800"
                                                    >
                                                        <td class="px-3 py-3">
                                                            <div class="b2b-shimmer h-4 w-4"></div>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-2.5">
                                                                <div class="b2b-shimmer h-10 w-10"></div>
                                                                <div class="b2b-shimmer h-3 w-40"></div>
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="b2b-shimmer h-3 w-20"></div>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="b2b-shimmer h-5 w-16"></div>
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="b2b-shimmer h-3 w-14"></div>
                                                        </td>

                                                        <td class="px-4 py-3"></td>
                                                    </tr>
                                                </template>

                                                <tr v-else-if="! modalProducts.length">
                                                    <td colspan="6" class="p-6 text-center text-sm text-gray-500">
                                                        @lang('b2b::app.admin.company-catalogs.no-products-found')
                                                    </td>
                                                </tr>

                                                <template v-else>
                                                    <tr
                                                        v-for="product in sortedModalProducts"
                                                        :key="product.id"
                                                        class="cursor-pointer border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                                        :class="{ 'opacity-60': isAssigned(product.id) }"
                                                        @click="toggleModalProduct(product)"
                                                    >
                                                        <td class="px-3 py-3">
                                                            <input
                                                                type="checkbox"
                                                                class="pointer-events-none"
                                                                :checked="isChecked(product.id)"
                                                                :disabled="isAssigned(product.id)"
                                                            >
                                                        </td>

                                                        <td class="px-4 py-3">
                                                            <div class="flex items-center gap-2.5">
                                                                <img
                                                                    class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800"
                                                                    :src="product.image"
                                                                >

                                                                <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                            </div>
                                                        </td>

                                                        <td class="px-4 py-3 text-xs text-gray-500">@{{ product.sku }}</td>

                                                        <td class="px-4 py-3">
                                                            <span class="inline-block rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                                @{{ typeLabels[product.type] ?? product.type }}
                                                            </span>
                                                        </td>

                                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">@{{ product.formatted_price }}</td>

                                                        <td class="px-4 py-3 text-right">
                                                            <span
                                                                v-if="isAssigned(product.id)"
                                                                class="whitespace-nowrap text-xs font-medium text-green-600"
                                                            >
                                                                @lang('b2b::app.admin.company-catalogs.added')
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Modal pagination -->
                                <div
                                    v-if="modalLastPage > 1"
                                    class="mt-3 flex items-center justify-between text-sm"
                                >
                                    <span class="text-gray-500 dark:text-gray-400">
                                        @{{ modalTotal }} @lang('b2b::app.admin.company-catalogs.products')
                                    </span>

                                    <div class="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                            :class="{ 'cursor-not-allowed opacity-50': modalPage === 1 }"
                                            :disabled="modalPage === 1"
                                            @click="fetchModalProducts(modalPage - 1)"
                                        >
                                            <span class="icon-sort-left rtl:icon-sort-right text-xl"></span>
                                        </button>

                                        <span class="px-2 text-gray-600 dark:text-gray-300">@{{ modalPage }} / @{{ modalLastPage }}</span>

                                        <button
                                            type="button"
                                            class="grid h-8 w-8 place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                            :class="{ 'cursor-not-allowed opacity-50': modalPage === modalLastPage }"
                                            :disabled="modalPage === modalLastPage"
                                            @click="fetchModalProducts(modalPage + 1)"
                                        >
                                            <span class="icon-sort-right rtl:icon-sort-left text-xl"></span>
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </x-slot>

                            <x-slot:footer>
                                <button
                                    type="button"
                                    class="primary-button"
                                    @click="assignSelected"
                                >
                                    @lang('b2b::app.admin.company-catalogs.assign')
                                    <span v-if="modalSelectedCount">(@{{ modalSelectedCount }})</span>
                                </button>
                            </x-slot>
                        </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-company-catalog', {
            template: '#v-company-catalog-template',

            data() {
                return {
                    products: @json($initialProducts),
                    companies: @json($initialCompanies),
                    companyQuery: '',
                    companyResults: [],
                    companyTimer: null,
                    massAction: 'update-price',
                    massType: 'fixed',
                    massValue: '',
                    currencySymbol: @json($currencySymbol),
                    currentPage: 1,
                    perPage: 10,
                    sortKey: '',
                    sortOrder: 'asc',
                    productsLoading: false,
                    addingCount: 0,

                    /* Assign-products modal */
                    modalQuery: '',
                    modalType: '',
                    modalSort: '',
                    modalOrder: 'asc',
                    typeLabels: @json($productTypes),
                    modalProducts: [],
                    modalSelectedProducts: {},
                    modalPage: 1,
                    modalLastPage: 1,
                    modalTotal: 0,
                    modalLoading: false,
                    modalTimer: null,
                };
            },

            created() {
                this.linkSharedLeaves();
            },

            watch: {
                'products.length'() {
                    if (this.currentPage > this.totalPages) {
                        this.currentPage = Math.max(1, this.totalPages);
                    }
                },
            },

            computed: {
                /* leaf id -> the products that contain it (one price shared across them). */
                leafIndex() {
                    const index = {};

                    this.products.forEach((product) => {
                        product.leaves.forEach((leaf) => {
                            (index[leaf.id] ??= []).push({ id: product.id, name: product.name });
                        });
                    });

                    return index;
                },

                selectedCount() {
                    return this.products.filter(p => p.selected).length;
                },

                allSelected() {
                    return this.paginatedProducts.length > 0
                        && this.paginatedProducts.every(p => p.selected);
                },

                totalPages() {
                    return Math.ceil(this.products.length / this.perPage);
                },

                /* Products in display order (optionally sorted by name/sku). */
                sortedProducts() {
                    if (! this.sortKey) {
                        return this.products;
                    }

                    const factor = this.sortOrder === 'asc' ? 1 : -1;

                    return [...this.products].sort((a, b) => {
                        const av = (a[this.sortKey] ?? '').toString().toLowerCase();
                        const bv = (b[this.sortKey] ?? '').toString().toLowerCase();

                        return av.localeCompare(bv, undefined, { numeric: true }) * factor;
                    });
                },

                paginatedProducts() {
                    const start = (this.currentPage - 1) * this.perPage;

                    return this.sortedProducts.slice(start, start + this.perPage);
                },

                modalSelectedCount() {
                    return Object.keys(this.modalSelectedProducts).length;
                },

                /* The fetched modal page, optionally sorted client-side by name/sku. */
                sortedModalProducts() {
                    if (! this.modalSort) {
                        return this.modalProducts;
                    }

                    const factor = this.modalOrder === 'asc' ? 1 : -1;

                    return [...this.modalProducts].sort((a, b) => {
                        const av = (a[this.modalSort] ?? '').toString().toLowerCase();
                        const bv = (b[this.modalSort] ?? '').toString().toLowerCase();

                        return av.localeCompare(bv, undefined, { numeric: true }) * factor;
                    });
                },

                modalAllChecked() {
                    const assignable = this.modalProducts.filter(p => ! this.isAssigned(p.id));

                    return assignable.length > 0
                        && assignable.every(p => !! this.modalSelectedProducts[p.id]);
                },
            },

            methods: {
                /**
                 * Resolve the resulting catalog price for a leaf (flat value, or base
                 * price minus the discount percentage). Mirrors the core price indexer.
                 */
                newPrice(leaf) {
                    const product = leaf;
                    const value = parseFloat(product.price_value);

                    if (product.price_value === '' || isNaN(value) || value < 0) {
                        return '—';
                    }

                    let price = value;

                    if (product.price_type === 'discount') {
                        if (value > 100) {
                            return '—';
                        }

                        price = product.price - (product.price * value / 100);
                    }

                    if (price < 0) {
                        price = 0;
                    }

                    return this.currencySymbol + price.toFixed(2);
                },

                /* Run the chosen mass action behind a confirmation modal. */
                runMassAction() {
                    if (! this.massAction || ! this.selectedCount) {
                        return;
                    }

                    if (this.massAction === 'update-price') {
                        if (this.massValue === '' || isNaN(parseFloat(this.massValue))) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: "@lang('b2b::app.admin.company-catalogs.enter-price-value')",
                            });

                            return;
                        }

                        this.$emitter.emit('open-confirm-modal', {
                            message: "@lang('b2b::app.admin.company-catalogs.confirm-update-price')".replace(':count', this.selectedCount),
                            agree: () => this.applyMass(),
                        });

                        return;
                    }

                    if (this.massAction === 'delete') {
                        this.$emitter.emit('open-confirm-modal', {
                            message: "@lang('b2b::app.admin.company-catalogs.confirm-delete')".replace(':count', this.selectedCount),
                            agree: () => this.removeSelected(),
                        });
                    }
                },

                applyMass() {
                    /* Apply to the selected products' leaves (or every product when none selected). */
                    const targets = this.selectedCount
                        ? this.products.filter(p => p.selected)
                        : this.products;

                    targets.forEach(product => {
                        product.leaves.forEach(leaf => {
                            leaf.price_type = this.massType;
                            leaf.price_value = this.massValue;
                        });
                    });

                    /* Reset selection so the mass-action bar hides (v-if="selectedCount"). */
                    this.products.forEach(p => p.selected = false);

                    this.massValue = '';
                    this.massAction = 'update-price';
                },

                toggleAll(event) {
                    const checked = event.target.checked;

                    this.paginatedProducts.forEach(p => p.selected = checked);
                },

                /* Remove every checked product from the catalog. */
                removeSelected() {
                    this.products = this.products.filter(p => ! p.selected);

                    this.massAction = 'update-price';
                },

                /* Client-side sort of the assigned products by a column. */
                sortBy(key) {
                    if (this.sortKey === key) {
                        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortKey = key;
                        this.sortOrder = 'asc';
                    }

                    this.currentPage = 1;
                },

                /* Icon class for an assigned-table column header (neutral when not the active sort). */
                sortIcon(key) {
                    if (this.sortKey !== key) {
                        return 'icon-sort text-gray-400';
                    }

                    return this.sortOrder === 'asc' ? 'icon-sort-up text-blue-500' : 'icon-sort-down text-blue-500';
                },


                /* Client-side sort of the current modal page by name/sku. */
                sortModalBy(key) {
                    if (this.modalSort === key) {
                        this.modalOrder = this.modalOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.modalSort = key;
                        this.modalOrder = 'asc';
                    }
                },

                /* Icon class for a modal column header (neutral when not the active sort). */
                modalSortIcon(key) {
                    if (this.modalSort !== key) {
                        return 'icon-sort text-gray-400';
                    }

                    return this.modalOrder === 'asc' ? 'icon-sort-up text-blue-500' : 'icon-sort-down text-blue-500';
                },

                /**
                 * A catalog price is one row per (leaf, group). When the same leaf appears
                 * under more than one assigned product (e.g. a simple sold standalone AND
                 * inside a bundle), point every occurrence at a SINGLE reactive leaf object
                 * so the price is edited once, shown consistently and submitted once. A
                 * newly added overlapping product adopts the already-set shared price.
                 */
                linkSharedLeaves() {
                    const shared = {};

                    this.products.forEach((product) => {
                        product.leaves = product.leaves.map((leaf) => {
                            if (shared[leaf.id]) {
                                return shared[leaf.id];
                            }

                            shared[leaf.id] = leaf;

                            return leaf;
                        });
                    });
                },

                isSharedLeaf(leafId) {
                    return (this.leafIndex[leafId]?.length ?? 0) > 1;
                },

                openAssignModal() {
                    this.modalQuery = '';
                    this.modalType = '';
                    this.modalSort = '';
                    this.modalOrder = 'asc';
                    this.modalSelectedProducts = {};
                    this.modalProducts = [];

                    this.$refs.assignProductsModal?.open();

                    this.$nextTick(() => this.fetchModalProducts(1));
                },

                searchModalProducts() {
                    clearTimeout(this.modalTimer);

                    this.modalTimer = setTimeout(() => this.fetchModalProducts(1), 400);
                },

                fetchModalProducts(page) {
                    if (page < 1) {
                        return;
                    }

                    this.modalLoading = true;

                    this.$axios.get("{{ route('admin.b2b.company_catalogs.products') }}", {
                        params: { query: this.modalQuery, type: this.modalType, page },
                    }).then((response) => {
                        this.modalProducts = response.data.data ?? [];
                        this.modalPage = response.data.meta?.current_page ?? 1;
                        this.modalLastPage = response.data.meta?.last_page ?? 1;
                        this.modalTotal = response.data.meta?.total ?? 0;
                        this.modalLoading = false;
                    }).catch((error) => {
                        this.modalLoading = false;

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message ?? 'Product search failed.',
                        });
                    });
                },

                isAssigned(id) {
                    return this.products.some(p => p.id === id);
                },

                isChecked(id) {
                    return this.isAssigned(id) || !! this.modalSelectedProducts[id];
                },

                toggleModalProduct(product) {
                    if (this.isAssigned(product.id)) {
                        return;
                    }

                    if (this.modalSelectedProducts[product.id]) {
                        delete this.modalSelectedProducts[product.id];
                    } else {
                        this.modalSelectedProducts[product.id] = product;
                    }
                },

                toggleModalAll(event) {
                    const checked = event.target.checked;

                    this.modalProducts.forEach((product) => {
                        if (this.isAssigned(product.id)) {
                            return;
                        }

                        if (checked) {
                            this.modalSelectedProducts[product.id] = product;
                        } else {
                            delete this.modalSelectedProducts[product.id];
                        }
                    });
                },

                assignSelected() {
                    const toAdd = Object.values(this.modalSelectedProducts)
                        .filter(product => ! this.isAssigned(product.id));

                    if (! toAdd.length) {
                        this.$refs.assignProductsModal.close();

                        return;
                    }

                    /* Skeleton rows show in the assigned table while the nodes are fetched. */
                    this.productsLoading = true;
                    this.addingCount = toAdd.length;

                    /**
                     * Each product is resolved into a node (type + price-bearing leaves)
                     * so composite types (configurable/grouped/bundle) expand to their
                     * children and booking stays visibility-only.
                     */
                    const requests = toAdd.map(product => this.$axios
                        .get("{{ route('admin.b2b.company_catalogs.product_children', ':id') }}".replace(':id', product.id))
                        .then(response => response.data?.data)
                        .catch(() => null)
                    );

                    /* Keep the skeleton visible for a short minimum so the load reads clearly. */
                    const minDelay = new Promise(resolve => setTimeout(resolve, 500));

                    Promise.all([Promise.all(requests), minDelay]).then(([nodes]) => {
                        nodes.filter(Boolean).forEach((node) => {
                            if (this.isAssigned(node.id)) {
                                return;
                            }

                            this.products.push({
                                id: node.id,
                                sku: node.sku,
                                name: node.name,
                                type: node.type,
                                image: node.image,
                                priceable: node.priceable,
                                is_composite: node.is_composite,
                                expanded: false,
                                selected: false,
                                leaves: (node.leaves ?? []).map(leaf => ({
                                    id: leaf.id,
                                    sku: leaf.sku,
                                    name: leaf.name,
                                    price: parseFloat(leaf.price) || 0,
                                    formatted_price: leaf.formatted_price,
                                    price_type: leaf.price_type ?? 'fixed',
                                    price_value: (leaf.price_value ?? '') === '' ? '' : parseFloat(leaf.price_value),
                                })),
                            });
                        });

                        /* Fold any overlapping leaves onto a single shared price object. */
                        this.linkSharedLeaves();

                        this.currentPage = 1;
                    }).finally(() => {
                        this.productsLoading = false;
                        this.addingCount = 0;
                    });

                    this.modalSelectedProducts = {};

                    this.$refs.assignProductsModal.close();
                },

                removeProduct(id) {
                    this.products = this.products.filter(p => p.id !== id);
                },

                searchCompanies() {
                    clearTimeout(this.companyTimer);

                    if (this.companyQuery.length < 2) {
                        this.companyResults = [];

                        return;
                    }

                    this.companyTimer = setTimeout(() => {
                        this.$axios.get("{{ route('admin.b2b.company_catalogs.companies') }}", {
                            params: { query: this.companyQuery },
                        }).then((response) => {
                            const rows = response.data?.data ?? response.data ?? [];

                            const selected = this.companies.map(c => c.id);

                            this.companyResults = rows
                                .filter(c => ! selected.includes(c.id))
                                .map(c => ({
                                    id: c.id,
                                    name: c.name,
                                    email: c.email,
                                    current_catalog: c.current_catalog,
                                }));
                        }).catch((error) => {
                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: error.response?.data?.message ?? 'Company search failed.',
                            });
                        });
                    }, 400);
                },

                addCompany(company) {
                    if (! this.companies.find(c => c.id === company.id)) {
                        this.companies.push(company);
                    }

                    this.companyResults = [];
                    this.companyQuery = '';
                },

                removeCompany(id) {
                    this.companies = this.companies.filter(c => c.id !== id);
                },
            },
        });
    </script>
@endPushOnce
