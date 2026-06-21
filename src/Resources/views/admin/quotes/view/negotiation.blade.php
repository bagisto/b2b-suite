@php
    $negotiationItems = $quote->items->map(fn ($item) => [
        'id'             => $item->id,
        'name'           => $item->name,
        'sku'            => $item->sku,
        'image'          => $item->product
            ? product_image()->getProductBaseImage($item->product)['small_image_url']
            : null,
        'price'          => (float) $item->price,
        'qty'            => (int) $item->qty,
        'negotiated_qty' => (int) ($item->negotiated_qty ?: $item->qty),
        'discount_type'  => $item->discount_type ?: 'percent',
        'discount_value' => $item->discount_value !== null ? (float) $item->discount_value : '',
        'removed'        => false,
    ])->values();

    // This is a revision whenever the buyer has already engaged — the quote moved past
    // "open" (negotiation/accepted/rejected) or the buyer requested/submitted it (any
    // customer message). Only an admin-created quote with no buyer activity reads "Send Quotation".
    $isRevision = in_array($quote->status, ['negotiation', 'accepted', 'rejected'])
        || $quote->messages()->where('user_type', 'customer')->exists();
@endphp

@pushOnce('styles')
    <style>
        /* The negotiation/preview modal is a little wider than the default (the editor
           holds an items table). Core's max-md:w-[90%] still caps it on mobile. */
        .box-shadow:has(.b2b-quote-modal) {
            max-width: 56rem !important;

            /* Standard tall-modal layout: cap the card to the viewport and turn it into a
               flex column so the header and footer stay pinned while only the body scrolls
               (instead of the whole card — and the Send button — scrolling off-screen). */
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        /* The body is the wrapper div that directly holds the editor form — it is the only
           scroll region; min-height:0 lets a flex child shrink so overflow can kick in. */
        .box-shadow:has(.b2b-quote-modal) > div:has(> .b2b-quote-modal) {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }

        /* The trigger button lives inside the Vue template, so <v-quote-negotiation> is empty
           until Vue mounts. The shimmer placeholder holds the button's space meanwhile; the
           moment the component mounts (Vue fills or replaces the tag, so the :empty match is
           gone) the placeholder is removed — no layout shift. */
        .b2b-negotiation-slot:not(:has(v-quote-negotiation:empty)) .b2b-negotiation-shimmer {
            display: none !important;
        }
    </style>
@endPushOnce

<div class="b2b-negotiation-slot" style="display: inline-flex;">
    <span
        class="b2b-negotiation-shimmer shimmer rounded-md"
        style="display: inline-block; height: 40px; width: 170px;"
    ></span>

    <v-quote-negotiation
        action="{{ route('admin.b2b.quotes.submit_quote', $quote->id) }}"
        currency="{{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}"
        :items='@json($negotiationItems)'
        total-discount-type="{{ $quote->discount_type ?: 'percent' }}"
        total-discount-value="{{ $quote->discount_value !== null ? (float) $quote->discount_value : '' }}"
    ></v-quote-negotiation>
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-quote-negotiation-template"
    >
        <div>
            <button
                type="button"
                class="primary-button"
                @click="openEditor"
            >
                @lang('b2b::app.admin.quotes.view.'.($isRevision ? 'revise-quotation' : 'send-quotation'))
            </button>

            <x-admin::modal ref="negotiationModal">
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('b2b::app.admin.quotes.view.'.($isRevision ? 'revise-quotation' : 'send-quotation'))
                    </p>
                </x-slot>

                <x-slot:content>
                    <form
                        ref="form"
                        class="b2b-quote-modal"
                        :action="action"
                        method="post"
                    >
                        @csrf

                        <!-- Removed line items (deleted on send) -->
                        <template v-for="id in removedIds" :key="'removed-' + id">
                            <input
                                type="hidden"
                                name="removed_items[]"
                                :value="id"
                            >
                        </template>

                        <!-- ============ STEP 1: EDIT ============ -->
                        <div v-show="step === 'edit'">
                            <p class="mb-4 text-sm text-gray-500 dark:text-gray-300">
                                @lang('b2b::app.admin.quotes.view.send-quotation-info')
                            </p>

                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                <table class="w-full" style="min-width: 46rem;">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr class="ltr:text-left rtl:text-right text-xs font-medium uppercase text-gray-500">
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.name')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.price')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.discount')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.negotiated-price')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.quantity')</th>
                                            <th class="px-4 py-3 ltr:text-right rtl:text-left">@lang('b2b::app.admin.quotes.view.sub-total')</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="row in activeRows"
                                            :key="row.id"
                                            class="border-t border-gray-100 dark:border-gray-800"
                                        >
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2.5">
                                                    <img
                                                        class="h-10 w-10 shrink-0 rounded border border-gray-100 object-cover dark:border-gray-800"
                                                        :src="row.image || placeholder"
                                                        v-on:error="onImageError"
                                                    >

                                                    <div class="grid">
                                                        <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ row.name }}</span>
                                                        <span class="text-xs text-gray-500">@{{ row.sku }}</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                                @{{ money(row.price) }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-1.5">
                                                    <div class="inline-flex shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                                        <button
                                                            type="button"
                                                            class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                            :class="row.discount_type === 'percent' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                                            @click="row.discount_type = 'percent'"
                                                        >%</button>

                                                        <button
                                                            type="button"
                                                            class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                            :class="row.discount_type === 'fixed' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                                            @click="row.discount_type = 'fixed'"
                                                        >@{{ currency }}</button>
                                                    </div>

                                                    <input
                                                        type="number" min="0" step="0.01"
                                                        class="w-20 rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                        placeholder="0"
                                                        v-model="row.discount_value"
                                                        :name="`items[${row.id}][discount_value]`"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        :name="`items[${row.id}][discount_type]`"
                                                        :value="row.discount_type"
                                                    >
                                                </div>
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white">
                                                @{{ money(discountedPrice(row)) }}
                                            </td>

                                            <td class="px-4 py-3">
                                                <input
                                                    type="number" min="1" step="1"
                                                    class="w-20 rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                                    v-model="row.negotiated_qty"
                                                    :name="`items[${row.id}][negotiated_qty]`"
                                                >
                                            </td>

                                            <td class="whitespace-nowrap px-4 py-3 ltr:text-right rtl:text-left text-sm font-semibold text-gray-800 dark:text-white">
                                                @{{ money(rowTotal(row)) }}
                                            </td>

                                            <td class="px-4 py-3 ltr:text-right rtl:text-left">
                                                <span
                                                    class="icon-delete cursor-pointer text-xl text-gray-500 transition-all hover:text-red-600"
                                                    title="@lang('b2b::app.admin.quotes.view.remove-item')"
                                                    @click="removeRow(row)"
                                                ></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals + whole-quote discount -->
                            <div class="mt-4 flex flex-col items-end gap-2">
                                <div class="flex w-full max-w-sm items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.sub-total')</span>
                                    <span class="font-medium text-gray-800 dark:text-white">@{{ money(subtotal) }}</span>
                                </div>

                                <div class="flex w-full max-w-sm items-center justify-between gap-3 text-sm">
                                    <span class="text-gray-500 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.discount-on-total')</span>

                                    <div class="flex items-center gap-1.5">
                                        <div class="inline-flex shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                            <button
                                                type="button"
                                                class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                :class="totalType === 'percent' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                                @click="totalType = 'percent'"
                                            >%</button>

                                            <button
                                                type="button"
                                                class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                :class="totalType === 'fixed' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
                                                @click="totalType = 'fixed'"
                                            >@{{ currency }}</button>
                                        </div>

                                        <input
                                            type="number" min="0" step="0.01"
                                            class="w-24 rounded-lg border border-gray-200 px-2.5 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                            placeholder="0"
                                            v-model="totalValue"
                                            name="total_discount_value"
                                        >

                                        <input
                                            type="hidden"
                                            name="total_discount_type"
                                            :value="totalType"
                                        >
                                    </div>
                                </div>

                                <div
                                    v-if="totalDiscountAmount > 0"
                                    class="flex w-full max-w-sm items-center justify-between text-sm"
                                >
                                    <span class="text-gray-500 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.you-save')</span>
                                    <span class="font-medium text-green-600">- @{{ money(totalDiscountAmount) }}</span>
                                </div>

                                <div class="flex w-full max-w-sm items-center justify-between border-t border-gray-200 pt-2 text-base dark:border-gray-800">
                                    <span class="font-bold text-gray-800 dark:text-white">@lang('b2b::app.admin.quotes.view.negotiated-total')</span>
                                    <span class="font-bold text-gray-800 dark:text-white">@{{ money(grandTotal) }}</span>
                                </div>
                            </div>

                            <!-- Message -->
                            <div class="mt-4">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    @lang('b2b::app.admin.quotes.view.send-message')
                                </label>

                                <textarea
                                    class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    rows="3"
                                    name="message"
                                    v-model="message"
                                    placeholder="@lang('b2b::app.admin.quotes.view.message-placeholder')"
                                ></textarea>
                            </div>
                        </div>

                        <!-- ============ STEP 2: PREVIEW ============ -->
                        <div v-show="step === 'preview'">
                            <div
                                class="mb-4 flex items-start gap-2 rounded-lg p-3"
                                style="background-color: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.25);"
                            >
                                <span class="icon-information shrink-0 text-xl text-blue-500"></span>
                                <p class="text-sm text-gray-700 dark:text-gray-200">
                                    @lang('b2b::app.admin.quotes.view.review-info')
                                </p>
                            </div>

                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                                <table class="w-full" style="min-width: 34rem;">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr class="ltr:text-left rtl:text-right text-xs font-medium uppercase text-gray-500">
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.name')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.negotiated-price')</th>
                                            <th class="px-4 py-3">@lang('b2b::app.admin.quotes.view.quantity')</th>
                                            <th class="px-4 py-3 ltr:text-right rtl:text-left">@lang('b2b::app.admin.quotes.view.sub-total')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="row in activeRows"
                                            :key="'preview-' + row.id"
                                            class="border-t border-gray-100 dark:border-gray-800"
                                        >
                                            <td class="px-4 py-2.5">
                                                <span class="text-sm font-medium text-gray-800 dark:text-white">@{{ row.name }}</span>
                                                <span class="block text-xs text-gray-500">@{{ row.sku }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200">@{{ money(discountedPrice(row)) }}</td>
                                            <td class="px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200">@{{ row.negotiated_qty }}</td>
                                            <td class="whitespace-nowrap px-4 py-2.5 ltr:text-right rtl:text-left text-sm font-semibold text-gray-800 dark:text-white">@{{ money(rowTotal(row)) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4 flex flex-col items-end gap-2">
                                <div class="flex w-full max-w-sm items-center justify-between text-sm">
                                    <span class="text-gray-500 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.sub-total')</span>
                                    <span class="font-medium text-gray-800 dark:text-white">@{{ money(subtotal) }}</span>
                                </div>

                                <div
                                    v-if="totalDiscountAmount > 0"
                                    class="flex w-full max-w-sm items-center justify-between text-sm"
                                >
                                    <span class="text-gray-500 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.discount-on-total')</span>
                                    <span class="font-medium text-green-600">- @{{ money(totalDiscountAmount) }}</span>
                                </div>

                                <div class="flex w-full max-w-sm items-center justify-between border-t border-gray-200 pt-2 text-base dark:border-gray-800">
                                    <span class="font-bold text-gray-800 dark:text-white">@lang('b2b::app.admin.quotes.view.negotiated-total')</span>
                                    <span class="font-bold text-gray-800 dark:text-white">@{{ money(grandTotal) }}</span>
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg border border-gray-200 p-3 dark:border-gray-800">
                                <span class="text-xs uppercase tracking-wide text-gray-400">@lang('b2b::app.admin.quotes.view.send-message')</span>
                                <p class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200" style="white-space: pre-line;">@{{ message }}</p>
                            </div>
                        </div>
                    </form>
                </x-slot>

                <x-slot:footer>
                    <!-- Edit step -->
                    <button
                        v-show="step === 'edit'"
                        type="button"
                        class="primary-button"
                        @click="review"
                    >
                        @lang('b2b::app.admin.quotes.view.next')
                    </button>

                    <!-- Preview step -->
                    <button
                        v-show="step === 'preview'"
                        type="button"
                        class="secondary-button"
                        @click="step = 'edit'"
                    >
                        @lang('b2b::app.admin.quotes.view.back')
                    </button>

                    <button
                        v-show="step === 'preview'"
                        type="button"
                        class="primary-button ltr:ml-2.5 rtl:mr-2.5"
                        @click="$refs.form.submit()"
                    >
                        @lang('b2b::app.admin.quotes.view.'.($isRevision ? 'confirm-revise' : 'confirm-send'))
                    </button>
                </x-slot>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        app.component('v-quote-negotiation', {
            template: '#v-quote-negotiation-template',

            props: ['action', 'currency', 'items', 'totalDiscountType', 'totalDiscountValue'],

            data() {
                return {
                    rows: this.items.map(item => ({ ...item })),
                    totalType: this.totalDiscountType || 'percent',
                    totalValue: this.totalDiscountValue ?? '',
                    message: '',
                    step: 'edit',
                    placeholder: "{{ bagisto_asset('images/product-placeholders/front.svg') }}",
                };
            },

            computed: {
                /**
                 * Lines still part of the quotation (a removed line is excluded from the
                 * table, the totals and the submitted item set).
                 */
                activeRows() {
                    return this.rows.filter(row => ! row.removed);
                },

                removedIds() {
                    return this.rows.filter(row => row.removed).map(row => row.id);
                },

                /**
                 * Subtotal across the active lines after each line's own discount.
                 */
                subtotal() {
                    return this.activeRows.reduce((sum, row) => sum + this.rowTotal(row), 0);
                },

                /**
                 * The currency amount taken off by the whole-quote discount.
                 */
                totalDiscountAmount() {
                    const value = parseFloat(this.totalValue);

                    if (! value || value <= 0) {
                        return 0;
                    }

                    if (this.totalType === 'percent') {
                        return Math.min(this.subtotal, this.subtotal * Math.min(value, 100) / 100);
                    }

                    return Math.min(this.subtotal, value);
                },

                grandTotal() {
                    return this.subtotal - this.totalDiscountAmount;
                },
            },

            methods: {
                /**
                 * Per-unit price after a line's own discount (percentage or fixed amount).
                 */
                discountedPrice(row) {
                    const price = parseFloat(row.price) || 0;
                    const value = parseFloat(row.discount_value);

                    if (! value || value <= 0) {
                        return price;
                    }

                    if (row.discount_type === 'percent') {
                        return Math.max(0, price - (price * Math.min(value, 100) / 100));
                    }

                    return Math.max(0, price - value);
                },

                rowTotal(row) {
                    return this.discountedPrice(row) * (parseInt(row.negotiated_qty) || 0);
                },

                money(value) {
                    return this.$admin.formatPrice(value);
                },

                /**
                 * Open the editor fresh (always starting on the edit step).
                 */
                openEditor() {
                    this.step = 'edit';

                    this.$refs.negotiationModal.open();
                },

                /**
                 * Drop a line from the quotation (never the last remaining one).
                 */
                removeRow(row) {
                    if (this.activeRows.length <= 1) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.admin.quotes.view.min-one-item')",
                        });

                        return;
                    }

                    row.removed = true;
                },

                /**
                 * Swap a broken/missing product image for the placeholder (once, no loop).
                 */
                onImageError(event) {
                    if (event.target.dataset.fallback) {
                        return;
                    }

                    event.target.dataset.fallback = '1';
                    event.target.src = this.placeholder;
                },

                /**
                 * Validate the edited offer, then move to the final preview/confirmation.
                 */
                review() {
                    if (! this.message || ! this.message.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.admin.quotes.view.message-required')",
                        });

                        return;
                    }

                    if (this.activeRows.some(row => ! (parseInt(row.negotiated_qty) >= 1))) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.admin.quotes.view.qty-invalid')",
                        });

                        return;
                    }

                    this.step = 'preview';
                },
            },
        });
    </script>
@endPushOnce
