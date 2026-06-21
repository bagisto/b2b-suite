{{--
    Storefront quote submission modal with a per-item discount editor (mirrors the admin
    "Send Quotation" editor, minus the whole-quote "Discount on Total"). Two modes:
      • draft   — name + description + item editor (initial draft submission)
      • counter — item editor + message (re-submit / counter-offer during negotiation)
    Submits items[id][discount_type|discount_value|negotiated_qty] (the shape
    CustomerQuoteRepository::createOrUpdateMessageQuotation expects) via a native form submit.
--}}
@php
    $mode ??= 'counter';
    $buttonClass ??= 'primary-button';

    $editorItems = $quote->items->map(fn ($item) => [
        'id'             => $item->id,
        'name'           => $item->name,
        'sku'            => $item->sku,
        'image'          => $item->product
            ? product_image()->getProductBaseImage($item->product)['small_image_url']
            : null,
        'price'          => (float) $item->price,
        'negotiated_qty' => (int) ($item->negotiated_qty ?: $item->qty),
        'discount_type'  => $item->discount_type ?: 'percent',
        'discount_value' => $item->discount_value !== null ? (float) $item->discount_value : '',
    ])->values();
@endphp

@pushOnce('styles')
    <style>
        /* The submission modal holds an items editor: make it a little wider, cap it to the
           viewport, and let only the body scroll (so the header + footer stay pinned and the
           Confirm button never falls off-screen). */
        .max-w-\[595px\]:has(.b2b-shop-quote-editor) {
            max-width: 52rem !important;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }

        /* The body is the wrapper div that directly holds the editor form — the only scroll
           region; min-height:0 lets a flex child shrink so overflow can kick in. */
        .max-w-\[595px\]:has(.b2b-shop-quote-editor) > div:has(> .b2b-shop-quote-editor) {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
        }
    </style>
@endPushOnce

<v-quote-submission
    action="{{ route('shop.customers.account.quotes.submit_quote', $quote->id) }}"
    mode="{{ $mode }}"
    currency="{{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}"
    button-label="{{ $buttonLabel ?? trans('b2b::app.shop.customers.account.quotes.view.btn-submit-quote') }}"
    button-class="{{ $buttonClass }}"
    quote-name="{{ $quote->name }}"
    quote-description="{{ $quote->description }}"
    :items='@json($editorItems)'
></v-quote-submission>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-quote-submission-template"
    >
        <x-shop::modal>
            <x-slot:toggle>
                <div
                    :class="buttonClass"
                    class="place-self-end rounded-lg px-11 py-3 max-md:max-w-full max-md:rounded-lg max-md:text-sm max-sm:w-full"
                >
                    @{{ buttonLabel }}
                </div>
            </x-slot>

            <x-slot:header>
                <h2 class="text-2xl font-medium max-md:text-base">
                    @lang('b2b::app.shop.customers.account.quotes.view.submit-quote')
                </h2>
            </x-slot>

            <x-slot:content>
                <form
                    ref="form"
                    :action="action"
                    method="post"
                    class="b2b-shop-quote-editor"
                >
                    @csrf

                    <!-- Draft: name + description (filled at creation) -->
                    <template v-if="mode === 'draft'">
                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-zinc-700">
                                @lang('b2b::app.shop.customers.account.quotes.view.quote-name')
                            </label>

                            <input
                                type="text"
                                name="name"
                                v-model="name"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2.5 text-sm"
                            >
                        </div>

                        <div class="mb-4">
                            <label class="mb-1 block text-sm font-medium text-zinc-700">
                                @lang('b2b::app.shop.customers.account.quotes.view.quote-description')
                            </label>

                            <textarea
                                name="description"
                                v-model="description"
                                rows="3"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2.5 text-sm"
                            ></textarea>
                        </div>
                    </template>

                    <!-- Per-item discount editor -->
                    <div class="overflow-x-auto rounded-lg border border-zinc-200">
                        <table class="w-full" style="min-width: 40rem;">
                            <thead class="bg-zinc-50">
                                <tr class="text-left text-xs font-medium uppercase text-zinc-500">
                                    <th class="px-4 py-3">@lang('b2b::app.shop.customers.account.quotes.view.name')</th>
                                    <th class="px-4 py-3">@lang('b2b::app.shop.customers.account.quotes.view.price')</th>
                                    <th class="px-4 py-3">@lang('b2b::app.shop.customers.account.quotes.view.discount')</th>
                                    <th class="px-4 py-3">@lang('b2b::app.shop.customers.account.quotes.view.negotiated-price')</th>
                                    <th class="px-4 py-3">@lang('b2b::app.shop.customers.account.quotes.view.quantity')</th>
                                    <th class="px-4 py-3 text-right">@lang('b2b::app.shop.customers.account.quotes.view.sub-total')</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="row in rows"
                                    :key="row.id"
                                    class="border-t border-zinc-100"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2.5">
                                            <img
                                                v-if="row.image"
                                                class="h-10 w-10 shrink-0 rounded border border-zinc-100 object-cover"
                                                :src="row.image"
                                            >
                                            <div
                                                v-else
                                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded border border-zinc-200 bg-zinc-100 text-xs font-medium text-zinc-500"
                                            >
                                                N/A
                                            </div>

                                            <div class="grid">
                                                <span class="text-sm font-medium text-zinc-800">@{{ row.name }}</span>
                                                <span class="text-xs text-zinc-500">@{{ row.sku }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-zinc-600">@{{ money(row.price) }}</td>

                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <div class="inline-flex shrink-0 overflow-hidden rounded-md border border-zinc-200">
                                                <button
                                                    type="button"
                                                    class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                    :class="row.discount_type === 'percent' ? 'text-white' : 'text-zinc-600 hover:bg-zinc-100'"
                                                    :style="row.discount_type === 'percent' ? 'background-color: #2563eb;' : ''"
                                                    @click="row.discount_type = 'percent'"
                                                >%</button>

                                                <button
                                                    type="button"
                                                    class="px-2 py-1.5 text-xs font-semibold transition-all"
                                                    :class="row.discount_type === 'fixed' ? 'text-white' : 'text-zinc-600 hover:bg-zinc-100'"
                                                    :style="row.discount_type === 'fixed' ? 'background-color: #2563eb;' : ''"
                                                    @click="row.discount_type = 'fixed'"
                                                >@{{ currency }}</button>
                                            </div>

                                            <input
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="w-20 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-sm"
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

                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-zinc-800">@{{ money(discountedPrice(row)) }}</td>

                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            min="1"
                                            step="1"
                                            class="w-20 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-sm"
                                            v-model="row.negotiated_qty"
                                            :name="`items[${row.id}][negotiated_qty]`"
                                        >
                                    </td>

                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-zinc-800">@{{ money(rowTotal(row)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Negotiated total (no whole-quote discount) -->
                    <div class="mt-4 flex flex-col items-end gap-2">
                        <div class="flex w-full max-w-xs items-center justify-between border-t border-zinc-200 pt-2 text-base">
                            <span class="font-bold text-zinc-800">@lang('b2b::app.shop.customers.account.quotes.view.negotiated-total')</span>
                            <span class="font-bold text-zinc-800">@{{ money(grandTotal) }}</span>
                        </div>
                    </div>

                    <!-- Counter: message to the seller -->
                    <template v-if="mode === 'counter'">
                        <div class="mt-4">
                            <label class="mb-1 block text-sm font-medium text-zinc-700">
                                @lang('b2b::app.shop.customers.account.quotes.view.send-message')
                            </label>

                            <textarea
                                name="message"
                                v-model="message"
                                rows="3"
                                class="w-full rounded-lg border border-zinc-200 px-3 py-2.5 text-sm"
                                placeholder="@lang('b2b::app.shop.customers.account.quotes.view.message-placeholder')"
                            ></textarea>
                        </div>
                    </template>
                </form>
            </x-slot>

            <x-slot:footer class="flex justify-end">
                <button
                    type="button"
                    class="primary-button flex rounded-2xl px-11 py-3 max-md:rounded-lg max-md:px-6 max-md:text-sm"
                    @click="submit"
                >
                    @lang('b2b::app.shop.customers.account.quotes.view.btn-confirm-send')
                </button>
            </x-slot>
        </x-shop::modal>
    </script>

    <script type="module">
        app.component('v-quote-submission', {
            template: '#v-quote-submission-template',

            props: ['action', 'mode', 'currency', 'buttonLabel', 'buttonClass', 'quoteName', 'quoteDescription', 'items'],

            data() {
                return {
                    rows: this.items.map(item => ({ ...item })),
                    name: this.quoteName || '',
                    description: this.quoteDescription || '',
                    message: '',
                };
            },

            computed: {
                grandTotal() {
                    return this.rows.reduce((sum, row) => sum + this.rowTotal(row), 0);
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
                    return this.$shop.formatPrice(value);
                },

                /**
                 * Validate the buyer's input, then submit the form natively (so the editor's
                 * raw inputs are posted).
                 */
                submit() {
                    if (this.mode === 'draft' && ! this.name.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.shop.customers.account.quotes.view.name-required')",
                        });

                        return;
                    }

                    if (this.mode === 'counter' && ! this.message.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.shop.customers.account.quotes.view.message-required')",
                        });

                        return;
                    }

                    if (this.rows.some(row => ! (parseInt(row.negotiated_qty) >= 1))) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('b2b::app.shop.customers.account.quotes.view.qty-invalid')",
                        });

                        return;
                    }

                    this.$refs.form.submit();
                },
            },
        });
    </script>
@endPushOnce
