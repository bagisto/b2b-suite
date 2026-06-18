@php
    $catalog ??= null;
    $products ??= collect();
    $companies ??= collect();
    $mode ??= 'create';
    // Read-only when a non-creator (non-super-admin) opens a shared catalog. Create is always editable.
    $canEdit ??= true;

    $normalizeLeaf = fn($l) => [
        'id' => $l['id'],
        'sku' => $l['sku'],
        'name' => $l['name'],
        'price' => (float) ($l['price'] ?? 0),
        'formatted_price' => $l['formatted_price'],
        'price_type' => $l['price_type'] ?? 'fixed',
        'price_value' => ($l['price_value'] ?? '') === '' ? '' : (float) $l['price_value'],
        'breaks' => collect($l['breaks'] ?? [])
            ->map(
                fn($b) => [
                    'qty' => (int) $b['qty'],
                    'type' => $b['type'] ?? 'fixed',
                    'value' => (float) $b['value'],
                ],
            )
            ->values(),
        'selected' => false,
    ];

    $initialProducts = collect($products)
        ->map(
            fn($p) => [
                'id' => $p['id'],
                'sku' => $p['sku'],
                'name' => $p['name'],
                'type' => $p['type'] ?? 'simple',
                'image' => $p['image'] ?? null,
                'priceable' => (bool) ($p['priceable'] ?? true),
                'is_composite' => (bool) ($p['is_composite'] ?? false),
                'expanded' => false,
                'selected' => false,
                'leaves' => collect($p['leaves'] ?? [])
                    ->map($normalizeLeaf)
                    ->values(),
            ],
        )
        ->values();

    $initialCompanies = collect($companies)
        ->map(
            fn($c) => [
                'id' => $c['id'],
                'name' => $c['name'],
                'email' => $c['email'],
                'selected' => false,
            ],
        )
        ->values();

    $currencySymbol = core()->currencySymbol(core()->getBaseCurrencyCode());

    $placeholderImage = bagisto_asset('images/product-placeholders/front.svg');

    $productTypes = collect(config('product_types'))
        ->mapWithKeys(fn($type, $code) => [$code => trans($type['name'])])
        ->all();
@endphp

@push('styles')
    <style>
        /**
         * Widen only the Assign Products modal (scoped via its marker), without
         * touching the core modal component.
         */
        .box-shadow:has(.b2b-assign-modal) {
            max-width: 64rem !important;
        }

        /**
         * The tier-pricing modal is a touch wider than the default (but narrower
         * than the assign modal). Core's max-md:w-[90%] still caps it to the
         * viewport on mobile.
         */
        .box-shadow:has(.b2b-tier-modal) {
            max-width: 44rem !important;
        }

        /**
         * Stack the save-confirmation modal's two panels on small screens. This is
         * a scoped rule because the `max-md:flex-col` utility is purged out of the
         * B2B theme bundle.
         */
        @media (max-width: 767px) {
            .b2b-preview-cols {
                flex-direction: column;
            }
        }

        /**
         * Skeleton shimmer for the loading state.
         */
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
            0% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
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
            @lang('b2b::app.admin.company-catalogs.' . $mode . '.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                href="{{ route('admin.b2b.company_catalogs.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('b2b::app.admin.company-catalogs.' . $mode . '.back-btn')
            </a>

            @if ($canEdit)
                <button
                    type="button"
                    class="primary-button"
                    @click="$emitter.emit('b2b-open-category-preview')"
                >
                    @lang('b2b::app.admin.company-catalogs.' . $mode . '.save-btn')
                </button>
            @endif
        </div>
    </div>

    @unless ($canEdit)
        <!-- Read-only notice for non-creator viewers of a shared catalog. -->
        <div
            class="mt-4 flex items-start gap-2 rounded-lg p-3"
            style="background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);"
        >
            <span class="icon-information shrink-0 text-xl" style="color: #f59e0b;"></span>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                @lang('b2b::app.admin.company-catalogs.read-only-note')
            </p>
        </div>
    @endunless

    <v-company-catalog :can-edit="{{ $canEdit ? 'true' : 'false' }}"></v-company-catalog>
</x-admin::form>

@pushOnce('scripts')
    <script type="text/x-template" id="v-company-catalog-template">
        <div>
            <!-- Main Content -->
            <div class="mt-3.5 flex flex-col gap-2">
                <!-- Products & Companies (full width) -->
                <div class="flex flex-col gap-2">
                    <!-- Products -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('b2b::app.admin.company-catalogs.products')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Info + Count (Left) · Pagination + Assign (Right) -->
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        @lang('b2b::app.admin.company-catalogs.products-info')
                                    </p>

                                    <p
                                        v-if="products.length"
                                        class="mt-1 text-xs text-gray-400"
                                    >
                                        @{{ "@lang('b2b::app.admin.company-catalogs.products-count')".replace(':count', products.length) }}<span v-if="childCount"> · @{{ "@lang('b2b::app.admin.company-catalogs.variants-count')".replace(':count', childCount) }}</span>
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    <x-b2b::table.pagination
                                        page="currentPage"
                                        total="totalPages"
                                        prev="currentPage--"
                                        next="currentPage++"
                                    />

                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="secondary-button flex items-center gap-1.5 !rounded-lg"
                                        @click="openAssignModal"
                                    >
                                        <span class="icon-plus text-lg"></span>

                                        @lang('b2b::app.admin.company-catalogs.assign-products')
                                    </button>
                                </div>
                            </div>

                            <!-- Mass Action Bar (Shown only when products are selected and the catalog is editable.) -->
                            <div
                                v-if="selectedCount && canEdit"
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

                                    <div class="relative">
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            v-model="massValue"
                                            class="w-24 rounded-lg border border-gray-200 px-3 py-1.5 text-sm ltr:pr-7 rtl:pl-7 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                            placeholder="0"
                                        >

                                        <span class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                    </div>
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

                            <!-- Selected Products -->
                            <div class="mt-3 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                <table
                                    class="w-full"
                                    style="table-layout: fixed; min-width: 52rem;"
                                >
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
                                                    v-if="canEdit"
                                                    type="checkbox"
                                                    class="cursor-pointer"
                                                    :checked="allSelected"
                                                    @change="toggleAll($event)"
                                                >
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortBy('name')"
                                                >
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
                                        <tr v-if="! products.length && ! productsLoading">
                                            <td
                                                colspan="7"
                                                class="px-4 py-6 text-center text-sm text-gray-500"
                                            >
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
                                                    <input
                                                        v-if="canEdit"
                                                        type="checkbox"
                                                        class="cursor-pointer"
                                                        :checked="isLeafSelected(product, product.leaves[0])"
                                                        @change="setLeafSelected(product, product.leaves[0], $event.target.checked)"
                                                    >
                                                </td>

                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2.5">
                                                        <img
                                                            class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800"
                                                            :src="product.image || placeholderImage"
                                                            v-on:error="onImageError"
                                                        >

                                                        <div class="grid">
                                                            <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                            <span class="text-xs text-gray-500">@{{ product.sku }}</span>

                                                            <span
                                                                v-if="canEdit"
                                                                class="mt-1 cursor-pointer text-xs font-medium text-blue-500 hover:underline"
                                                                @click="openTierModal(product.leaves[0], product.image)"
                                                            >
                                                                @lang('b2b::app.admin.company-catalogs.volume-pricing')<span v-if="breakCount(product.leaves[0])"> (@{{ breakCount(product.leaves[0]) }})</span>
                                                            </span>

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
                                                    <select
                                                        v-if="canEdit"
                                                        class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                        v-model="product.leaves[0].price_type"
                                                    >
                                                        <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                                        <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                                    </select>
                                                    <span v-else class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                                </td>

                                                <td class="px-4 py-3">
                                                    <div v-if="canEdit" class="relative w-28">
                                                        <input
                                                            type="number" step="0.01" min="0"
                                                            class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                            :class="product.leaves[0].price_type === 'discount' ? 'ltr:pr-7 rtl:pl-7' : ''"
                                                            v-model="product.leaves[0].price_value"
                                                            :placeholder="product.leaves[0].price_type === 'discount' ? '0' : '@lang('b2b::app.admin.company-catalogs.price-placeholder')'"
                                                        >
                                                        <span v-if="product.leaves[0].price_type === 'discount'" class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                                    </div>
                                                    <span v-else class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                                </td>

                                                <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white">@{{ newPrice(product.leaves[0]) }}</td>

                                                <td class="px-4 py-3 text-right">
                                                    <span v-if="canEdit" class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                                </td>
                                            </tr>

                                            <!-- Booking: Visibility Only (Core never reads group prices for booking.) -->
                                            <tr
                                                v-else-if="! product.priceable"
                                                class="border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                            >
                                                <td class="px-3 py-3">
                                                    <input
                                                        v-if="canEdit"
                                                        type="checkbox"
                                                        class="cursor-pointer"
                                                        :checked="isBookingSelected(product)"
                                                        @change="setBookingSelected(product, $event.target.checked)"
                                                    >
                                                </td>

                                                <td class="px-4 py-3">
                                                    <div class="flex items-center gap-2.5">
                                                        <img
                                                            class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800"
                                                            :src="product.image || placeholderImage"
                                                            v-on:error="onImageError"
                                                        >

                                                        <div class="grid">
                                                            <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                            <span class="text-xs text-gray-500">@{{ product.sku }} · @{{ product.type }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td
                                                    colspan="4"
                                                    class="px-4 py-3 text-sm italic text-gray-400"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.visibility-only')
                                                </td>

                                                <td class="px-4 py-3 text-right">
                                                    <span v-if="canEdit" class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                                </td>
                                            </tr>

                                            <!-- Composite (Configurable / Grouped / Bundle): Header Row + Expandable Leaf Prices -->
                                            <template v-else>
                                                <tr class="border-t border-gray-100 bg-gray-50 transition-all dark:border-gray-800 dark:bg-gray-950">
                                                    <td class="px-3 py-3">
                                                        <input
                                                            v-if="canEdit"
                                                            type="checkbox"
                                                            class="cursor-pointer"
                                                            :checked="isProductSelected(product)"
                                                            @change="toggleProduct(product, $event.target.checked)"
                                                        >
                                                    </td>

                                                    <td class="px-4 py-3">
                                                        <div class="flex items-center gap-2.5">
                                                            <button
                                                                type="button"
                                                                class="grid h-6 w-6 shrink-0 place-items-center rounded text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-800"
                                                                @click="product.expanded = ! product.expanded"
                                                            >
                                                                <span :class="product.expanded ? 'icon-arrow-up' : 'icon-arrow-down'" class="text-xl"></span>
                                                            </button>

                                                            <img
                                                                class="h-10 w-10 rounded border border-gray-100 object-cover dark:border-gray-800"
                                                                :src="product.image || placeholderImage"
                                                                v-on:error="onImageError"
                                                            >

                                                            <div class="grid">
                                                                <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ product.name }}</span>
                                                                <span class="text-xs text-gray-500">@{{ product.sku }} · @{{ product.type }}</span>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td
                                                        colspan="4"
                                                        class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400"
                                                    >
                                                        <span
                                                            class="cursor-pointer hover:underline"
                                                            @click="product.expanded = ! product.expanded"
                                                        >
                                                            @{{ "@lang('b2b::app.admin.company-catalogs.leaf-count')".replace(':count', product.leaves.length) }}
                                                        </span>
                                                    </td>

                                                    <td class="px-4 py-3 text-right">
                                                        <span v-if="canEdit" class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeProduct(product.id)"></span>
                                                    </td>
                                                </tr>

                                                <tr
                                                    v-show="product.expanded"
                                                    v-for="leaf in product.leaves"
                                                    :key="leaf.id"
                                                    class="border-t border-gray-100 dark:border-gray-800"
                                                >
                                                    <td class="px-3 py-2.5">
                                                        <input
                                                            v-if="canEdit"
                                                            type="checkbox"
                                                            class="cursor-pointer"
                                                            :checked="isLeafSelected(product, leaf)"
                                                            @change="setLeafSelected(product, leaf, $event.target.checked)"
                                                        >
                                                    </td>

                                                    <td class="px-4 py-2.5">
                                                        <div class="grid ps-8">
                                                            <span class="text-sm text-gray-700 dark:text-gray-200">@{{ leaf.name }}</span>
                                                            <span class="text-xs text-gray-500">@{{ leaf.sku }}</span>

                                                            <span
                                                                v-if="canEdit"
                                                                class="mt-1 cursor-pointer text-xs font-medium text-blue-500 hover:underline"
                                                                @click="openTierModal(leaf, product.image)"
                                                            >
                                                                @lang('b2b::app.admin.company-catalogs.volume-pricing')<span v-if="breakCount(leaf)"> (@{{ breakCount(leaf) }})</span>
                                                            </span>

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
                                                        <select
                                                            v-if="canEdit"
                                                            class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                            v-model="leaf.price_type"
                                                        >
                                                            <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                                            <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                                        </select>
                                                        <span v-else class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                                    </td>

                                                    <td class="px-4 py-2.5">
                                                        <div v-if="canEdit" class="relative w-28">
                                                            <input
                                                                type="number" step="0.01" min="0"
                                                                class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                                :class="leaf.price_type === 'discount' ? 'ltr:pr-7 rtl:pl-7' : ''"
                                                                v-model="leaf.price_value"
                                                                :placeholder="leaf.price_type === 'discount' ? '0' : '@lang('b2b::app.admin.company-catalogs.price-placeholder')'"
                                                            >
                                                            <span v-if="leaf.price_type === 'discount'" class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                                        </div>
                                                        <span v-else class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                                    </td>

                                                    <td class="whitespace-nowrap px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-white">@{{ newPrice(leaf) }}</td>

                                                    <td class="px-4 py-2.5"></td>
                                                </tr>
                                            </template>
                                        </template>

                                        <!-- Shimmer Rows While Newly Assigned Products Are Being Fetched -->
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

                            <!--
                                All assigned products' fields are submitted here (not just the
                                visible page), so pagination never drops a product or its price.
                            -->
                            <template
                                v-for="product in products"
                                :key="'field-' + product.id"
                            >
                                <input
                                    type="hidden"
                                    name="products[]"
                                    :value="product.id"
                                >

                                <template
                                    v-for="leaf in product.leaves"
                                    :key="'leaf-field-' + leaf.id"
                                >
                                    <input
                                        type="hidden"
                                        :name="`prices[${leaf.id}][type]`"
                                        :value="leaf.price_type"
                                    >
                                    <input
                                        type="hidden"
                                        :name="`prices[${leaf.id}][value]`"
                                        :value="leaf.price_value"
                                    >

                                    <template
                                        v-for="(brk, i) in (leaf.breaks || [])"
                                        :key="'brk-field-' + leaf.id + '-' + i"
                                    >
                                        <input
                                            type="hidden"
                                            :name="`prices[${leaf.id}][breaks][${i}][qty]`"
                                            :value="brk.qty"
                                        >
                                        <input
                                            type="hidden"
                                            :name="`prices[${leaf.id}][breaks][${i}][type]`"
                                            :value="brk.type"
                                        >
                                        <input
                                            type="hidden"
                                            :name="`prices[${leaf.id}][breaks][${i}][value]`"
                                            :value="brk.value"
                                        >
                                    </template>
                                </template>
                            </template>
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
                            <!-- Info + Count (Left) · Pagination + Assign (Right) -->
                            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">
                                        @lang('b2b::app.admin.company-catalogs.companies-info')
                                    </p>

                                    <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                                        @lang('b2b::app.admin.company-catalogs.companies-single-note')
                                    </p>

                                    <p
                                        v-if="companies.length"
                                        class="mt-1 text-xs text-gray-400"
                                    >
                                        @{{ "@lang('b2b::app.admin.company-catalogs.companies-count')".replace(':count', companies.length) }}
                                    </p>
                                </div>

                                <div class="flex shrink-0 items-center gap-3">
                                    <x-b2b::table.pagination
                                        page="companyPage"
                                        total="companyTotalPages"
                                        prev="companyPage--"
                                        next="companyPage++"
                                    />

                                    <button
                                        v-if="canEdit"
                                        type="button"
                                        class="secondary-button flex items-center gap-1.5 !rounded-lg"
                                        @click="openCompanyModal"
                                    >
                                        <span class="icon-plus text-lg"></span>

                                        @lang('b2b::app.admin.company-catalogs.assign-companies')
                                    </button>
                                </div>
                            </div>

                            <!-- Mass Action Bar (editable catalogs only) -->
                            <div
                                v-if="companySelectedCount && canEdit"
                                class="mt-4 flex flex-wrap items-center gap-2.5 rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950"
                            >
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    @{{ "@lang('b2b::app.admin.company-catalogs.items-selected')".replace(':count', companySelectedCount) }}
                                </span>

                                <select
                                    v-model="companyMassAction"
                                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                >
                                    <option value="delete">@lang('b2b::app.admin.company-catalogs.delete-action')</option>
                                </select>

                                <button
                                    v-if="companyMassAction"
                                    type="button"
                                    class="secondary-button !rounded-lg !px-4 !py-1.5 text-sm"
                                    @click="runCompanyMassAction"
                                >
                                    @lang('b2b::app.admin.company-catalogs.apply')
                                </button>
                            </div>

                            <!-- Assigned Companies -->
                            <div class="mt-3 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                <table
                                    class="w-full"
                                    style="table-layout: fixed; min-width: 38rem;"
                                >
                                    <colgroup>
                                        <col style="width: 3rem;">
                                        <col>
                                        <col style="width: 18rem;">
                                        <col style="width: 3.5rem;">
                                    </colgroup>

                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                            <th class="px-3 py-4">
                                                <input
                                                    v-if="canEdit"
                                                    type="checkbox"
                                                    class="cursor-pointer"
                                                    :checked="companyAllSelected"
                                                    @change="toggleAllCompanies($event)"
                                                >
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortCompaniesBy('name')"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.company')
                                                    <span :class="companySortIcon('name')" class="text-base"></span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortCompaniesBy('email')"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.email')
                                                    <span :class="companySortIcon('email')" class="text-base"></span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-4"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-if="! companies.length && ! companiesLoading">
                                            <td
                                                colspan="4"
                                                class="px-4 py-6 text-center text-sm text-gray-500"
                                            >
                                                @lang('b2b::app.admin.company-catalogs.no-companies')
                                            </td>
                                        </tr>

                                        <tr
                                            v-for="company in paginatedCompanies"
                                            :key="company.id"
                                            class="border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                                        >
                                            <td class="px-3 py-3">
                                                <input
                                                    v-if="canEdit"
                                                    type="checkbox"
                                                    class="cursor-pointer"
                                                    v-model="company.selected"
                                                >
                                            </td>

                                            <td class="px-4 py-3">
                                                <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ company.name }}</span>
                                            </td>

                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">@{{ company.email }}</td>

                                            <td class="px-4 py-3 text-right">
                                                <span v-if="canEdit" class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeCompany(company.id)"></span>
                                            </td>
                                        </tr>

                                        <!-- Shimmer rows while newly assigned companies are being fetched. -->
                                        <template v-if="companiesLoading">
                                            <tr
                                                v-for="n in companyAddingCount"
                                                :key="'company-skeleton-' + n"
                                                class="border-t border-gray-100 dark:border-gray-800"
                                            >
                                                <td class="px-3 py-3"><div class="b2b-shimmer h-4 w-4"></div></td>
                                                <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-40"></div></td>
                                                <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-48"></div></td>
                                                <td class="px-4 py-3"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>

                            <!-- All assigned companies submitted (not just the visible page). -->
                            <template
                                v-for="company in companies"
                                :key="'company-field-' + company.id"
                            >
                                <input
                                    type="hidden"
                                    name="companies[]"
                                    :value="company.id"
                                >
                            </template>
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
                        <!-- Search + Type Filter -->
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

                        <!-- Product List (Fixed height so the modal doesn't resize between pages.) -->
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                            <div class="h-96 overflow-auto">
                                <table
                                    class="w-full"
                                    style="table-layout: fixed; min-width: 46rem;"
                                >
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
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortModalBy('name')"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.product')
                                                    <span :class="modalSortIcon('name')" class="text-base"></span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortModalBy('sku')"
                                                >
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
                                            <td
                                                colspan="6"
                                                class="p-6 text-center text-sm text-gray-500"
                                            >
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
                                                            :src="product.image || placeholderImage" v-on:error="onImageError"
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

                        <!-- Modal Pagination -->
                        <div
                            v-if="modalLastPage > 1"
                            class="mt-3 flex items-center justify-between text-sm"
                        >
                            <span class="text-gray-500 dark:text-gray-400">
                                @{{ modalTotal }} @lang('b2b::app.admin.company-catalogs.products')
                            </span>

                            <x-b2b::table.pagination
                                page="modalPage"
                                total="modalLastPage"
                                prev="fetchModalProducts(modalPage - 1)"
                                next="fetchModalProducts(modalPage + 1)"
                            />
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

            <!-- Assign Companies Modal -->
            <x-admin::modal ref="assignCompaniesModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('b2b::app.admin.company-catalogs.assign-companies')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="b2b-assign-modal">
                        <!-- Search -->
                        <div class="relative mb-4">
                            <span class="icon-search pointer-events-none absolute top-2.5 text-xl text-gray-400 ltr:left-3 rtl:right-3"></span>

                            <input
                                type="text"
                                class="w-full rounded-lg border border-gray-200 py-2.5 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white ltr:pl-10 rtl:pr-10"
                                placeholder="@lang('b2b::app.admin.company-catalogs.search-companies')"
                                v-model="companyModalQuery"
                                @input="searchModalCompanies"
                            >
                        </div>

                        <!-- Company List -->
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                            <div class="h-96 overflow-auto">
                                <table
                                    class="w-full"
                                    style="table-layout: fixed; min-width: 40rem;"
                                >
                                    <colgroup>
                                        <col style="width: 3rem;">
                                        <col>
                                        <col style="width: 18rem;">
                                        <col style="width: 8rem;">
                                    </colgroup>

                                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-800">
                                        <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                            <th class="px-3 py-4">
                                                <input
                                                    type="checkbox"
                                                    class="cursor-pointer"
                                                    :checked="companyModalAllChecked"
                                                    @change="toggleModalAllCompanies($event)"
                                                >
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortCompanyModalBy('name')"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.company')
                                                    <span :class="companyModalSortIcon('name')" class="text-base"></span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-4">
                                                <button
                                                    type="button"
                                                    class="flex items-center gap-1 uppercase text-gray-600 transition-all hover:text-gray-800 dark:text-gray-300 dark:hover:text-white"
                                                    @click="sortCompanyModalBy('email')"
                                                >
                                                    @lang('b2b::app.admin.company-catalogs.email')
                                                    <span :class="companyModalSortIcon('email')" class="text-base"></span>
                                                </button>
                                            </th>
                                            <th class="px-4 py-4"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <template v-if="companyModalLoading">
                                            <tr
                                                v-for="n in 6"
                                                :key="'company-modal-skeleton-' + n"
                                                class="border-t border-gray-100 dark:border-gray-800"
                                            >
                                                <td class="px-3 py-3"><div class="b2b-shimmer h-4 w-4"></div></td>
                                                <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-40"></div></td>
                                                <td class="px-4 py-3"><div class="b2b-shimmer h-3 w-48"></div></td>
                                                <td class="px-4 py-3"></td>
                                            </tr>
                                        </template>

                                        <tr v-else-if="! companyModalRows.length">
                                            <td
                                                colspan="4"
                                                class="p-6 text-center text-sm text-gray-500"
                                            >
                                                @lang('b2b::app.admin.company-catalogs.no-companies-found')
                                            </td>
                                        </tr>

                                        <template v-else>
                                            <tr
                                                v-for="company in companyModalRows"
                                                :key="company.id"
                                                class="cursor-pointer border-t border-gray-100 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800"
                                                :class="{ 'opacity-60': isCompanyAssigned(company.id) }"
                                                @click="toggleModalCompany(company)"
                                            >
                                                <td class="px-3 py-3">
                                                    <input
                                                        type="checkbox"
                                                        class="pointer-events-none"
                                                        :checked="isCompanyChecked(company.id)"
                                                        :disabled="isCompanyAssigned(company.id)"
                                                    >
                                                </td>

                                                <td class="px-4 py-3">
                                                    <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ company.name }}</span>

                                                    <span
                                                        v-if="company.current_catalog"
                                                        class="mt-0.5 block w-max rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-400"
                                                    >
                                                        @{{ "@lang('b2b::app.admin.company-catalogs.in-catalog')".replace(':name', company.current_catalog) }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">@{{ company.email }}</td>

                                                <td class="px-4 py-3 text-right">
                                                    <span
                                                        v-if="isCompanyAssigned(company.id)"
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

                        <!-- Modal Pagination -->
                        <div
                            v-if="companyModalLastPage > 1"
                            class="mt-3 flex items-center justify-between text-sm"
                        >
                            <span class="text-gray-500 dark:text-gray-400">
                                @{{ companyModalTotal }} @lang('b2b::app.admin.company-catalogs.companies')
                            </span>

                            <x-b2b::table.pagination
                                page="companyModalPage"
                                total="companyModalLastPage"
                                prev="fetchModalCompanies(companyModalPage - 1)"
                                next="fetchModalCompanies(companyModalPage + 1)"
                            />
                        </div>
                    </div>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="button"
                        class="primary-button"
                        @click="assignCompaniesSelected"
                    >
                        @lang('b2b::app.admin.company-catalogs.assign')
                        <span v-if="companyModalSelectedCount">(@{{ companyModalSelectedCount }})</span>
                    </button>
                </x-slot>
            </x-admin::modal>

            <!-- Save Confirmation: Category tree shown to the assigned companies. -->
            <x-admin::modal ref="categoryPreviewModal">
                <x-slot:header>
                    <div class="flex items-center gap-2.5">
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.company-catalogs.category-preview-title')
                        </p>

                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide text-blue-500"
                            style="background-color: rgba(59, 130, 246, 0.12);"
                        >
                            @lang('b2b::app.admin.company-catalogs.preview-badge')
                        </span>
                    </div>
                </x-slot>

                <x-slot:content>
                    <div class="b2b-assign-modal">
                        <!-- Preview Callout -->
                        <div
                            class="mb-5 flex items-start gap-3 rounded-lg p-4"
                            style="background-color: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25);"
                        >
                            <span class="icon-view shrink-0 text-3xl text-blue-500"></span>

                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                                    @lang('b2b::app.admin.company-catalogs.preview-banner-title')
                                </p>

                                <p class="mt-0.5 text-sm text-gray-600 dark:text-gray-300">
                                    @lang('b2b::app.admin.company-catalogs.category-preview-info')
                                </p>
                            </div>
                        </div>

                        <div class="b2b-preview-cols flex gap-4">
                            <!-- Derived Category Tree -->
                            <div class="flex-1">
                                <p class="mb-2 text-xs font-semibold uppercase text-gray-500">
                                    @lang('b2b::app.admin.company-catalogs.visible-categories')
                                    <span v-if="! categoryLoading" class="text-gray-400">(@{{ categoryTree.length }})</span>
                                </p>

                                <div class="relative mb-2">
                                    <span class="icon-search pointer-events-none absolute top-2.5 text-lg text-gray-400 ltr:left-3 rtl:right-3"></span>

                                    <input
                                        type="text"
                                        class="w-full rounded-lg border border-gray-200 py-2 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white ltr:pl-10 rtl:pr-10"
                                        placeholder="@lang('b2b::app.admin.company-catalogs.search-categories')"
                                        v-model="previewCategorySearch"
                                    >
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-800">
                                    <div
                                        class="overflow-y-auto p-2"
                                        style="height: 24rem;"
                                    >
                                        <template v-if="categoryLoading">
                                            <div
                                                v-for="n in 6"
                                                :key="'cat-sk-' + n"
                                                class="px-2 py-2"
                                            >
                                                <div class="b2b-shimmer h-3" :style="{ width: (160 - n * 14) + 'px' }"></div>
                                            </div>
                                        </template>

                                        <p
                                            v-else-if="! categoryTree.length"
                                            class="py-8 text-center text-sm text-gray-500"
                                        >
                                            @lang('b2b::app.admin.company-catalogs.no-categories-derived')
                                        </p>

                                        <p
                                            v-else-if="! filteredCategoryTree.length"
                                            class="py-8 text-center text-sm text-gray-500"
                                        >
                                            @lang('b2b::app.admin.company-catalogs.no-matches')
                                        </p>

                                        <template v-else>
                                            <div
                                                v-for="node in filteredCategoryTree"
                                                :key="node.id"
                                                class="flex items-center justify-between gap-2 rounded py-1.5 ltr:pr-2 rtl:pl-2 hover:bg-gray-50 dark:hover:bg-gray-800"
                                                :style="{ paddingInlineStart: (node.depth * 1.25 + 0.5) + 'rem' }"
                                            >
                                                <span
                                                    class="text-sm text-gray-700 dark:text-gray-200"
                                                    :class="node.depth === 0 ? 'font-medium text-gray-800 dark:text-white' : ''"
                                                >@{{ node.name }}</span>

                                                <span class="shrink-0 rounded bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                    @{{ node.count }}
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Companies that will see this. -->
                            <div class="w-72 max-w-full max-md:w-full">
                                <p class="mb-2 text-xs font-semibold uppercase text-gray-500">
                                    @lang('b2b::app.admin.company-catalogs.shown-to-companies')
                                    <span class="text-gray-400">(@{{ companies.length }})</span>
                                </p>

                                <div class="relative mb-2">
                                    <span class="icon-search pointer-events-none absolute top-2.5 text-lg text-gray-400 ltr:left-3 rtl:right-3"></span>

                                    <input
                                        type="text"
                                        class="w-full rounded-lg border border-gray-200 py-2 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-white ltr:pl-10 rtl:pr-10"
                                        placeholder="@lang('b2b::app.admin.company-catalogs.search-companies')"
                                        v-model="previewCompanySearch"
                                        @input="previewCompanyLimit = 9"
                                    >
                                </div>

                                <div class="rounded-lg border border-gray-200 dark:border-gray-800">
                                    <div
                                        class="overflow-y-auto p-2"
                                        style="height: 24rem;"
                                    >
                                        <p
                                            v-if="! companies.length"
                                            class="py-8 text-center text-sm text-gray-500"
                                        >
                                            @lang('b2b::app.admin.company-catalogs.no-companies')
                                        </p>

                                        <p
                                            v-else-if="! filteredPreviewCompanies.length"
                                            class="py-8 text-center text-sm text-gray-500"
                                        >
                                            @lang('b2b::app.admin.company-catalogs.no-matches')
                                        </p>

                                        <template v-else>
                                            <div
                                                v-for="company in visiblePreviewCompanies"
                                                :key="'cp-' + company.id"
                                                class="rounded px-2 py-1.5"
                                            >
                                                <span class="block text-sm font-medium text-gray-800 dark:text-white">@{{ company.name }}</span>
                                                <span class="block text-xs text-gray-500">@{{ company.email }}</span>
                                            </div>

                                            <button
                                                v-if="filteredPreviewCompanies.length > previewCompanyLimit"
                                                type="button"
                                                class="mt-2 w-full rounded-lg border border-gray-200 py-2 text-sm font-medium text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                                @click="loadMoreCompanies"
                                            >
                                                @{{ "@lang('b2b::app.admin.company-catalogs.load-more')".replace(':count', filteredPreviewCompanies.length - previewCompanyLimit) }}
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="submit"
                        class="primary-button"
                    >
                        @lang('b2b::app.admin.company-catalogs.confirm-save')
                    </button>
                </x-slot>
            </x-admin::modal>

            <!-- Volume Pricing (Tier) Modal -->
            <x-admin::modal ref="tierModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('b2b::app.admin.company-catalogs.volume-pricing')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div
                        v-if="tierModalLeaf"
                        class="b2b-tier-modal"
                    >
                        <!-- Product Details -->
                        <div class="mb-4 flex items-center gap-3">
                            <img
                                class="h-12 w-12 shrink-0 rounded border border-gray-100 object-cover dark:border-gray-800"
                                :src="tierModalImage || placeholderImage"
                                v-on:error="onImageError"
                            >

                            <div class="grid">
                                <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ tierModalLeaf.name }}</span>
                                <span class="text-xs text-gray-500">@{{ tierModalLeaf.sku }}</span>
                            </div>
                        </div>

                        <p class="mb-4 text-sm text-gray-500 dark:text-gray-300">
                            @lang('b2b::app.admin.company-catalogs.volume-pricing-info')
                        </p>

                        <!-- Base (qty 1) price for reference; edited inline on the row. -->
                        <div class="mb-4 flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3 dark:border-gray-800">
                            <span class="text-sm text-gray-600 dark:text-gray-300">@lang('b2b::app.admin.company-catalogs.base-price') (1+)</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-white">@{{ newPrice(tierModalLeaf) }}</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                            <table
                                class="w-full"
                                style="table-layout: fixed; min-width: 34rem;"
                            >
                                <colgroup>
                                    <col style="width: 7rem;">
                                    <col style="width: 9rem;">
                                    <col style="width: 8rem;">
                                    <col>
                                    <col style="width: 3.5rem;">
                                </colgroup>

                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr class="text-left text-xs font-medium uppercase text-gray-500">
                                        <th class="px-4 py-3">@lang('b2b::app.admin.company-catalogs.tier-qty')</th>
                                        <th class="px-4 py-3">@lang('b2b::app.admin.company-catalogs.price-type')</th>
                                        <th class="px-4 py-3">@lang('b2b::app.admin.company-catalogs.value')</th>
                                        <th class="px-4 py-3">@lang('b2b::app.admin.company-catalogs.unit-price')</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-if="! tierModalLeaf.breaks.length">
                                        <td
                                            colspan="5"
                                            class="px-4 py-6 text-center text-sm text-gray-500"
                                        >
                                            @lang('b2b::app.admin.company-catalogs.no-breaks')
                                        </td>
                                    </tr>

                                    <tr
                                        v-for="(brk, i) in tierModalLeaf.breaks"
                                        :key="'brk-' + i"
                                        class="border-t border-gray-100 dark:border-gray-800"
                                    >
                                        <td class="px-4 py-2.5">
                                            <input
                                                type="number" min="2" step="1"
                                                class="w-20 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                v-model="brk.qty"
                                            >
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <select
                                                class="rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                v-model="brk.type"
                                            >
                                                <option value="fixed">@lang('b2b::app.admin.company-catalogs.flat')</option>
                                                <option value="discount">@lang('b2b::app.admin.company-catalogs.discount')</option>
                                            </select>
                                        </td>

                                        <td class="px-4 py-2.5">
                                            <div class="relative w-28">
                                                <input
                                                    type="number" step="0.01" min="0"
                                                    class="w-28 rounded-lg border border-gray-200 px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    :class="brk.type === 'discount' ? 'ltr:pr-7 rtl:pl-7' : ''"
                                                    v-model="brk.value"
                                                    :placeholder="brk.type === 'discount' ? '0' : '@lang('b2b::app.admin.company-catalogs.price-placeholder')'"
                                                >
                                                <span v-if="brk.type === 'discount'" class="pointer-events-none absolute top-1.5 text-sm text-gray-400 ltr:right-3 rtl:left-3">%</span>
                                            </div>
                                        </td>

                                        <td class="whitespace-nowrap px-4 py-2.5 text-sm font-semibold text-gray-800 dark:text-white">@{{ unitPrice(tierModalLeaf, brk) }}</td>

                                        <td class="px-4 py-2.5 text-right">
                                            <span class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600" @click="removeBreak(i)"></span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 flex justify-end">
                            <button
                                type="button"
                                class="flex items-center gap-1 text-sm font-medium text-blue-500 transition-all hover:text-blue-600"
                                @click="addBreak"
                            >
                                <span class="text-base font-semibold">+</span>
                                @lang('b2b::app.admin.company-catalogs.add-price-break')
                            </button>
                        </div>
                    </div>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="button"
                        class="primary-button"
                        @click="$refs.tierModal.close()"
                    >
                        @lang('b2b::app.admin.company-catalogs.done')
                    </button>
                </x-slot>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-company-catalog', {
            template: '#v-company-catalog-template',

            props: {
                canEdit: {
                    type: Boolean,
                    default: true,
                },
            },

            data() {
                return {
                    products: @json($initialProducts),
                    companies: @json($initialCompanies),
                    companyMassAction: 'delete',
                    companyPage: 1,
                    companyPerPage: 10,
                    companySortKey: '',
                    companySortOrder: 'asc',
                    companiesLoading: false,
                    companyAddingCount: 0,

                    /**
                     * The assign-companies modal tracks its own pagination, sorting, and selection
                     * state.
                     */
                    companyModalQuery: '',
                    companyModalSort: '',
                    companyModalOrder: 'asc',
                    companyModalRows: [],
                    companyModalSelected: {},
                    companyModalPage: 1,
                    companyModalLastPage: 1,
                    companyModalTotal: 0,
                    companyModalLoading: false,
                    companyModalTimer: null,
                    massAction: 'update-price',
                    massType: 'fixed',
                    massValue: '',
                    currencySymbol: @json($currencySymbol),
                    placeholderImage: @json($placeholderImage),
                    currentPage: 1,
                    perPage: 10,
                    sortKey: '',
                    sortOrder: 'asc',
                    productsLoading: false,
                    addingCount: 0,

                    /**
                     * Selection is tracked per (product, leaf) — keyed "productId:leafId" —
                     * not on the leaf object, because shared leaves (a product assigned both
                     * standalone and inside a bundle) reference ONE object; keying by product
                     * keeps each occurrence's selection independent (so select-all only ever
                     * affects the current page).
                     */
                    selectedKeys: {},

                    /**
                     * The category tree shown in the "Save" confirmation modal, derived from the
                     * currently assigned products. Each node has "id", "name", "depth", and "count"
                     * (number of assigned products that reference it) properties.
                     */
                    categoryTree: [],
                    categoryLoading: false,
                    previewCategorySearch: '',
                    previewCompanySearch: '',
                    previewCompanyLimit: 9,

                    /**
                     * The assign-products modal tracks its own pagination, sorting, and selection
                     * state, separate from the main page's.
                     */
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

                    /**
                     * The leaf whose tier pricing is being edited in the modal (null when
                     * closed), plus the product image to show for it. The image is passed
                     * in on open because a shared leaf can appear under products with
                     * different images.
                     */
                    tierModalLeaf: null,
                    tierModalImage: null,
                };
            },

            created() {
                this.linkSharedLeaves();

                /**
                 * The top "Save" button opens the category preview instead of submitting.
                 */
                this.$emitter.on('b2b-open-category-preview', () => this.openCategoryPreview());
            },

            watch: {
                'products.length'() {
                    if (this.currentPage > this.totalPages) {
                        this.currentPage = Math.max(1, this.totalPages);
                    }
                },
            },

            computed: {
                /**
                 * Maps each leaf id to the products that contain it (a single price is
                 * shared across all of them).
                 */
                leafIndex() {
                    const index = {};

                    this.products.forEach((product) => {
                        product.leaves.forEach((leaf) => {
                            (index[leaf.id] ??= []).push({ id: product.id, name: product.name });
                        });
                    });

                    return index;
                },

                /**
                 * Every price-bearing leaf across all assigned products.
                 */
                allLeaves() {
                    return this.products.flatMap(p => p.leaves);
                },

                /**
                 * Total child rows (variants / associated / bundle products) across
                 * composites.
                 */
                childCount() {
                    return this.products.reduce((sum, p) => sum + (p.is_composite ? p.leaves.length : 0), 0);
                },

                selectedCount() {
                    return Object.keys(this.selectedKeys).length;
                },

                allSelected() {
                    const page = this.paginatedProducts;

                    return page.length > 0 && page.every(p => this.isProductSelected(p));
                },

                totalPages() {
                    return Math.ceil(this.products.length / this.perPage);
                },

                /**
                 * Products in display order (optionally sorted by name/sku).
                 */
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

                /**
                 * The fetched modal page, optionally sorted client-side by name/sku.
                 */
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

                /**
                 * Companies.
                 */
                companySelectedCount() {
                    return this.companies.filter(c => c.selected).length;
                },

                companyAllSelected() {
                    return this.paginatedCompanies.length > 0
                        && this.paginatedCompanies.every(c => c.selected);
                },

                companyTotalPages() {
                    return Math.ceil(this.companies.length / this.companyPerPage);
                },

                sortedCompanies() {
                    if (! this.companySortKey) {
                        return this.companies;
                    }

                    const factor = this.companySortOrder === 'asc' ? 1 : -1;

                    return [...this.companies].sort((a, b) => {
                        const av = (a[this.companySortKey] ?? '').toString().toLowerCase();
                        const bv = (b[this.companySortKey] ?? '').toString().toLowerCase();

                        return av.localeCompare(bv, undefined, { numeric: true }) * factor;
                    });
                },

                paginatedCompanies() {
                    const start = (this.companyPage - 1) * this.companyPerPage;

                    return this.sortedCompanies.slice(start, start + this.companyPerPage);
                },

                companyModalSelectedCount() {
                    return Object.keys(this.companyModalSelected).length;
                },

                companyModalAllChecked() {
                    const assignable = this.companyModalRows.filter(c => ! this.isCompanyAssigned(c.id));

                    return assignable.length > 0
                        && assignable.every(c => !! this.companyModalSelected[c.id]);
                },

                /**
                 * Save-confirmation preview.
                 */
                filteredCategoryTree() {
                    const query = this.previewCategorySearch.trim().toLowerCase();

                    if (! query) {
                        return this.categoryTree;
                    }

                    return this.categoryTree.filter(node => node.name.toLowerCase().includes(query));
                },

                filteredPreviewCompanies() {
                    const query = this.previewCompanySearch.trim().toLowerCase();

                    if (! query) {
                        return this.companies;
                    }

                    return this.companies.filter(company =>
                        (company.name || '').toLowerCase().includes(query)
                        || (company.email || '').toLowerCase().includes(query)
                    );
                },

                visiblePreviewCompanies() {
                    return this.filteredPreviewCompanies.slice(0, this.previewCompanyLimit);
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

                /**
                 * Resulting unit price for a volume break (fixed value, or base product
                 * price minus the discount %). Same math as newPrice(), but the break
                 * carries its own type/value while the unit price is always off leaf.price.
                 */
                unitPrice(leaf, tier) {
                    const value = parseFloat(tier.value);

                    if (tier.value === '' || tier.value === null || isNaN(value) || value < 0) {
                        return '—';
                    }

                    let price = value;

                    if (tier.type === 'discount') {
                        if (value > 100) {
                            return '—';
                        }

                        price = leaf.price - (leaf.price * value / 100);
                    }

                    if (price < 0) {
                        price = 0;
                    }

                    return this.currencySymbol + price.toFixed(2);
                },

                /**
                 * Count of volume breaks that carry a usable value (drives the row badge).
                 */
                breakCount(leaf) {
                    if (! leaf || ! Array.isArray(leaf.breaks)) {
                        return 0;
                    }

                    return leaf.breaks.filter(b => b.value !== '' && b.value !== null && ! isNaN(parseFloat(b.value))).length;
                },

                /**
                 * Swap a broken/missing product image for the placeholder (once, no loop).
                 */
                onImageError(event) {
                    if (event.target.dataset.fallback) {
                        return;
                    }

                    event.target.dataset.fallback = '1';
                    event.target.src = this.placeholderImage;
                },

                /**
                 * Open the tier-pricing modal for a leaf (mutated in place, reactively).
                 */
                openTierModal(leaf, image) {
                    if (! Array.isArray(leaf.breaks)) {
                        leaf.breaks = [];
                    }

                    this.tierModalLeaf = leaf;
                    this.tierModalImage = image ?? null;

                    this.$refs.tierModal?.open();
                },

                /**
                 * Append a new break, seeded one increment above the current highest qty.
                 */
                addBreak() {
                    const leaf = this.tierModalLeaf;

                    if (! leaf) {
                        return;
                    }

                    if (leaf.breaks.length >= 10) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.admin.company-catalogs.max-breaks')".replace(':count', 10),
                        });

                        return;
                    }

                    const maxQty = leaf.breaks.reduce((max, b) => Math.max(max, parseInt(b.qty) || 0), 1);

                    leaf.breaks.push({
                        qty: maxQty <= 1 ? 10 : maxQty + 10,
                        type: leaf.price_type || 'fixed',
                        value: '',
                    });
                },

                removeBreak(index) {
                    this.tierModalLeaf?.breaks.splice(index, 1);
                },

                /**
                 * Run the chosen mass action behind a confirmation modal.
                 */
                runMassAction() {
                    if (! this.massAction || ! this.selectedCount) {
                        return;
                    }

                    if (this.massAction === 'update-price') {
                        const percent = parseFloat(this.massValue);

                        if (this.massValue === '' || isNaN(percent) || percent < 0 || percent > 100) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: "@lang('b2b::app.admin.company-catalogs.enter-discount-percent')",
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

                /**
                 * Per-(product, leaf) selection keys.
                 */
                leafKey(product, leaf) {
                    return product.id + ':' + leaf.id;
                },

                isLeafSelected(product, leaf) {
                    return !! this.selectedKeys[this.leafKey(product, leaf)];
                },

                setLeafSelected(product, leaf, on) {
                    const key = this.leafKey(product, leaf);

                    if (on) {
                        this.selectedKeys[key] = true;
                    } else {
                        delete this.selectedKeys[key];
                    }
                },

                isBookingSelected(product) {
                    return !! this.selectedKeys[product.id + ':booking'];
                },

                setBookingSelected(product, on) {
                    const key = product.id + ':booking';

                    if (on) {
                        this.selectedKeys[key] = true;
                    } else {
                        delete this.selectedKeys[key];
                    }
                },

                applyMass() {
                    /**
                     * Apply to the selected leaves (or every leaf when nothing is selected).
                     */
                    const selected = [];

                    this.products.forEach(p => p.leaves.forEach(l => {
                        if (this.isLeafSelected(p, l)) {
                            selected.push(l);
                        }
                    }));

                    const targets = selected.length ? selected : this.allLeaves;

                    const percent = parseFloat(this.massValue);

                    /**
                     * The admin always enters a discount %. For "Discount" it is stored as-is
                     * (a percentage off). For "Flat" the % is applied to each product's own
                     * base price and the resulting amount is stored as that product's flat
                     * (fixed) catalog price — so one percentage yields the correct per-product
                     * price.
                     */
                    targets.forEach(leaf => {
                        if (this.massType === 'fixed') {
                            const base = parseFloat(leaf.price) || 0;

                            let discounted = base - (base * percent / 100);

                            if (discounted < 0) {
                                discounted = 0;
                            }

                            leaf.price_type = 'fixed';
                            leaf.price_value = Number(discounted.toFixed(2));
                        } else {
                            leaf.price_type = 'discount';
                            leaf.price_value = percent;
                        }
                    });

                    /**
                     * Reset selection so the mass-action bar hides (v-if="selectedCount").
                     */
                    this.selectedKeys = {};

                    this.massValue = '';
                    this.massAction = 'update-price';
                },

                /**
                 * Select-all toggles ONLY the current page (never other pages).
                 */
                toggleAll(event) {
                    const checked = event.target.checked;

                    this.paginatedProducts.forEach(p => this.toggleProduct(p, checked));
                },

                /**
                 * A product is selected when all its leaves are (or, for booking, the row
                 * itself).
                 */
                isProductSelected(product) {
                    if (! product.priceable) {
                        return this.isBookingSelected(product);
                    }

                    return product.leaves.length > 0
                        && product.leaves.every(l => this.isLeafSelected(product, l));
                },

                toggleProduct(product, checked) {
                    if (! product.priceable) {
                        this.setBookingSelected(product, checked);

                        return;
                    }

                    product.leaves.forEach(l => this.setLeafSelected(product, l, checked));
                },

                /**
                 * Delete removes a product if any of its leaves is checked (or a checked
                 * booking).
                 */
                removeSelected() {
                    this.products = this.products.filter(p => {
                        const leafSelected = p.leaves.some(l => this.isLeafSelected(p, l));
                        const bookingSelected = ! p.priceable && this.isBookingSelected(p);

                        return ! (leafSelected || bookingSelected);
                    });

                    this.selectedKeys = {};
                    this.massAction = 'update-price';
                },

                /**
                 * Client-side sort of the assigned products by a column.
                 */
                sortBy(key) {
                    if (this.sortKey === key) {
                        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortKey = key;
                        this.sortOrder = 'asc';
                    }

                    this.currentPage = 1;
                },

                /**
                 * Icon class for an assigned-table column header (neutral when not the
                 * active sort).
                 */
                sortIcon(key) {
                    if (this.sortKey !== key) {
                        return 'icon-sort text-gray-400';
                    }

                    return this.sortOrder === 'asc' ? 'icon-sort-up text-blue-500' : 'icon-sort-down text-blue-500';
                },


                /**
                 * Client-side sort of the current modal page by name/sku.
                 */
                sortModalBy(key) {
                    if (this.modalSort === key) {
                        this.modalOrder = this.modalOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.modalSort = key;
                        this.modalOrder = 'asc';
                    }
                },

                /**
                 * Icon class for a modal column header (neutral when not the active sort).
                 */
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

                    /**
                     * Skeleton rows show in the assigned table while the nodes are fetched.
                     */
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

                    /**
                     * Keep the skeleton visible for a short minimum so the load reads clearly.
                     */
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
                                    breaks: (leaf.breaks ?? []).map(b => ({
                                        qty: parseInt(b.qty) || 2,
                                        type: b.type ?? 'fixed',
                                        value: (b.value ?? '') === '' ? '' : parseFloat(b.value),
                                    })),
                                    selected: false,
                                })),
                            });
                        });

                        /**
                         * Fold any overlapping leaves onto a single shared price object.
                         */
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

                    Object.keys(this.selectedKeys).forEach(key => {
                        if (key.startsWith(id + ':')) {
                            delete this.selectedKeys[key];
                        }
                    });
                },

                /**
                 * Open the save-confirmation dialog with the categories derived from the
                 * currently assigned products. The dialog's button submits the form.
                 */
                openCategoryPreview() {
                    this.categoryTree = [];
                    this.categoryLoading = true;
                    this.previewCategorySearch = '';
                    this.previewCompanySearch = '';
                    this.previewCompanyLimit = 9;

                    this.$refs.categoryPreviewModal?.open();

                    this.$axios.post("{{ route('admin.b2b.company_catalogs.category_preview') }}", {
                        products: this.products.map(p => p.id),
                    }).then((response) => {
                        this.categoryTree = response.data.tree ?? [];
                        this.categoryLoading = false;
                    }).catch((error) => {
                        this.categoryLoading = false;

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message ?? 'Failed to load categories.',
                        });
                    });
                },

                loadMoreCompanies() {
                    this.previewCompanyLimit += 9;
                },

                /**
                 * Assigned companies table.
                 */
                companySortIcon(key) {
                    if (this.companySortKey !== key) {
                        return 'icon-sort text-gray-400';
                    }

                    return this.companySortOrder === 'asc' ? 'icon-sort-up text-blue-500' : 'icon-sort-down text-blue-500';
                },

                sortCompaniesBy(key) {
                    if (this.companySortKey === key) {
                        this.companySortOrder = this.companySortOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.companySortKey = key;
                        this.companySortOrder = 'asc';
                    }

                    this.companyPage = 1;
                },

                toggleAllCompanies(event) {
                    const checked = event.target.checked;

                    this.paginatedCompanies.forEach(c => c.selected = checked);
                },

                /**
                 * Run the chosen company mass action behind a confirmation modal.
                 */
                runCompanyMassAction() {
                    if (! this.companyMassAction || ! this.companySelectedCount) {
                        return;
                    }

                    if (this.companyMassAction === 'delete') {
                        this.$emitter.emit('open-confirm-modal', {
                            message: "@lang('b2b::app.admin.company-catalogs.confirm-remove-companies')".replace(':count', this.companySelectedCount),
                            agree: () => this.removeSelectedCompanies(),
                        });
                    }
                },

                removeSelectedCompanies() {
                    this.companies = this.companies.filter(c => ! c.selected);
                },

                removeCompany(id) {
                    this.companies = this.companies.filter(c => c.id !== id);
                },

                /**
                 * Assign-companies modal.
                 */
                openCompanyModal() {
                    this.companyModalQuery = '';
                    this.companyModalSort = '';
                    this.companyModalOrder = 'asc';
                    this.companyModalSelected = {};
                    this.companyModalRows = [];

                    this.$refs.assignCompaniesModal?.open();

                    this.$nextTick(() => this.fetchModalCompanies(1));
                },

                searchModalCompanies() {
                    clearTimeout(this.companyModalTimer);

                    this.companyModalTimer = setTimeout(() => this.fetchModalCompanies(1), 400);
                },

                fetchModalCompanies(page) {
                    if (page < 1) {
                        return;
                    }

                    this.companyModalLoading = true;

                    this.$axios.get("{{ route('admin.b2b.company_catalogs.companies') }}", {
                        params: { query: this.companyModalQuery, sort: this.companyModalSort, order: this.companyModalOrder, page },
                    }).then((response) => {
                        this.companyModalRows = response.data.data ?? [];
                        this.companyModalPage = response.data.meta?.current_page ?? 1;
                        this.companyModalLastPage = response.data.meta?.last_page ?? 1;
                        this.companyModalTotal = response.data.meta?.total ?? 0;
                        this.companyModalLoading = false;
                    }).catch((error) => {
                        this.companyModalLoading = false;

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message ?? 'Company search failed.',
                        });
                    });
                },

                sortCompanyModalBy(key) {
                    if (this.companyModalSort === key) {
                        this.companyModalOrder = this.companyModalOrder === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.companyModalSort = key;
                        this.companyModalOrder = 'asc';
                    }

                    this.fetchModalCompanies(1);
                },

                companyModalSortIcon(key) {
                    if (this.companyModalSort !== key) {
                        return 'icon-sort text-gray-400';
                    }

                    return this.companyModalOrder === 'asc' ? 'icon-sort-up text-blue-500' : 'icon-sort-down text-blue-500';
                },

                isCompanyAssigned(id) {
                    return this.companies.some(c => c.id === id);
                },

                isCompanyChecked(id) {
                    return this.isCompanyAssigned(id) || !! this.companyModalSelected[id];
                },

                toggleModalCompany(company) {
                    if (this.isCompanyAssigned(company.id)) {
                        return;
                    }

                    if (this.companyModalSelected[company.id]) {
                        delete this.companyModalSelected[company.id];
                    } else {
                        this.companyModalSelected[company.id] = company;
                    }
                },

                toggleModalAllCompanies(event) {
                    const checked = event.target.checked;

                    this.companyModalRows.forEach((company) => {
                        if (this.isCompanyAssigned(company.id)) {
                            return;
                        }

                        if (checked) {
                            this.companyModalSelected[company.id] = company;
                        } else {
                            delete this.companyModalSelected[company.id];
                        }
                    });
                },

                assignCompaniesSelected() {
                    const toAdd = Object.values(this.companyModalSelected)
                        .filter(company => ! this.isCompanyAssigned(company.id));

                    if (! toAdd.length) {
                        this.$refs.assignCompaniesModal.close();

                        return;
                    }

                    /**
                     * Skeleton rows show briefly while the additions settle.
                     */
                    this.companiesLoading = true;
                    this.companyAddingCount = toAdd.length;

                    new Promise(resolve => setTimeout(resolve, 500)).then(() => {
                        toAdd.forEach((company) => {
                            if (! this.isCompanyAssigned(company.id)) {
                                this.companies.push({
                                    id: company.id,
                                    name: company.name,
                                    email: company.email,
                                    selected: false,
                                });
                            }
                        });

                        this.companyPage = 1;
                    }).finally(() => {
                        this.companiesLoading = false;
                        this.companyAddingCount = 0;
                    });

                    this.companyModalSelected = {};

                    this.$refs.assignCompaniesModal.close();
                },
            },
        });
    </script>
@endPushOnce
