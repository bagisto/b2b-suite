@php
    $canSendMessage = ! in_array($quote->status, ['draft', 'purchase_order', 'expired', 'completed', 'accepted', 'rejected', 'ordered']);
@endphp

<quote-messages
    :initial-quote="{{ json_encode($quote) }}"
    :can-send-message="{{ $canSendMessage ? 'true' : 'false' }}"
></quote-messages>

@push('styles')
    <style>
        .b2b-chat-thread { display: flex; flex-direction: column; gap: 1.25rem; max-height: 440px; overflow-y: auto; padding: 0.5rem 0.25rem; }
        .b2b-chat-row { display: flex; align-items: flex-end; gap: 0.625rem; }
        .b2b-chat-row.is-mine { flex-direction: row-reverse; }
        .b2b-chat-avatar { height: 2.25rem; width: 2.25rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #F6F2EB; color: #060C3B; }
        .b2b-chat-row.is-mine .b2b-chat-avatar { background: #060C3B; color: #fff; }
        .b2b-chat-col { max-width: 76%; display: flex; flex-direction: column; }
        .b2b-chat-row.is-mine .b2b-chat-col { align-items: flex-end; }
        .b2b-chat-meta { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.6875rem; color: #a1a1aa; }
        .b2b-chat-meta b { color: #060C3B; font-weight: 600; }
        .b2b-chat-bubble { border-radius: 1rem; padding: 0.625rem 0.875rem; font-size: 0.875rem; line-height: 1.45; word-break: break-word; }
        .b2b-chat-bubble.is-mine { background: #060C3B; color: #fff; border-bottom-right-radius: 0.25rem; }
        .b2b-chat-bubble.is-theirs { background: #f4f4f5; color: #27272a; border-bottom-left-radius: 0.25rem; }
        .b2b-chat-text { white-space: pre-line; }
        .b2b-chat-quotations { margin-top: 0.5rem; border-radius: 0.625rem; background: #fff; color: #27272a; overflow: hidden; border: 1px solid #e4e4e7; }
        .b2b-chat-quotations table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }
        .b2b-chat-quotations th { background: #fafafa; text-align: left; padding: 0.5rem 0.625rem; font-weight: 600; }
        .b2b-chat-quotations td { padding: 0.5rem 0.625rem; border-top: 1px solid #f1f1f4; }
        .b2b-chat-status { display: inline-block; margin-top: 0.5rem; border-radius: 9999px; padding: 0.125rem 0.625rem; font-size: 0.6875rem; font-weight: 600; }
        .b2b-chat-status.is-rejected { background: #fde8e6; color: #b42318; }
        .b2b-chat-status.is-ok { background: #e7f6ea; color: #1f7a33; }
        .b2b-chat-composer { display: flex; align-items: flex-end; gap: 0.5rem; margin-top: 1rem; border-top: 1px solid #f1f1f4; padding-top: 1rem; }
        .b2b-chat-input { flex: 1; resize: none; border: 1px solid #d4d4d8; border-radius: 1rem; padding: 0.75rem 1rem; font-size: 0.875rem; outline: none; transition: border-color .2s; max-height: 140px; }
        .b2b-chat-input:focus { border-color: #060C3B; }
        .b2b-chat-send { height: 2.75rem; width: 2.75rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: #060C3B; color: #fff; cursor: pointer; transition: opacity .2s; }
        .b2b-chat-send:disabled { opacity: .45; cursor: not-allowed; }
    </style>
@endpush

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="quote-messages-template"
    >
        <div class="mt-4 flex flex-col rounded-xl border bg-white p-5 max-xl:flex-auto max-sm:p-3">
            <!-- Header -->
            <div class="mb-4 flex items-center justify-between gap-3 max-sm:flex-col max-sm:items-start">
                <p class="text-base font-semibold text-navyBlue">
                    @if ($quote->state == 'quotation')
                        @lang('b2b::app.shop.customers.account.quotes.view.quote-messages')
                    @else
                        @lang('b2b::app.shop.customers.account.purchase-orders.view.po-messages')
                    @endif
                </p>

                <!-- Filters -->
                <div class="flex items-center gap-2 max-sm:w-full max-sm:flex-wrap">
                    <select
                        v-model="filters.has_quotations"
                        @change="applyFilters"
                        class="rounded-lg border border-zinc-300 px-3 py-2 text-xs"
                    >
                        <option value="">All messages</option>
                        <option value="true">With quotations</option>
                    </select>

                    <select
                        v-model="filters.user_type"
                        @change="applyFilters"
                        class="rounded-lg border border-zinc-300 px-3 py-2 text-xs"
                    >
                        <option value="">Everyone</option>
                        <option value="customer">You</option>
                        <option value="admin">Seller</option>
                    </select>

                    <button
                        @click="clearFilters"
                        v-if="hasActiveFilters"
                        type="button"
                        class="text-xs font-medium text-navyBlue underline"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex justify-center py-6 text-sm text-zinc-500">
                @lang('b2b::app.shop.customers.account.quotes.view.loading-messages')
            </div>

            <!-- Thread -->
            <div v-else id="messages-panel" class="b2b-chat-thread">
                <div
                    v-for="msg in (messages.data ? messages.data : messages)"
                    :key="msg.id"
                    class="b2b-chat-row"
                    :class="{ 'is-mine': msg.user_type == 'customer' }"
                >
                    <!-- Avatar -->
                    <div class="b2b-chat-avatar">@{{ getInitials(msg.user_type) }}</div>

                    <div class="b2b-chat-col">
                        <!-- Meta -->
                        <div class="b2b-chat-meta">
                            <b>@{{ getUserTypeLabel(msg.user_type) }}</b>
                            <span>@{{ formatDate(msg.created_at) }}</span>
                        </div>

                        <!-- Bubble -->
                        <div
                            class="b2b-chat-bubble"
                            :class="msg.user_type == 'customer' ? 'is-mine' : 'is-theirs'"
                        >
                            <p class="b2b-chat-text" v-if="msg.message">@{{ msg.message }}</p>

                            <!-- Quotations -->
                            <div v-if="msg.quotations && msg.quotations.length > 0" class="b2b-chat-quotations">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>@lang('b2b::app.shop.customers.account.quotes.view.name')</th>
                                            <th>@lang('b2b::app.shop.customers.account.quotes.view.price')</th>
                                            <th>@lang('b2b::app.shop.customers.account.quotes.view.discount')</th>
                                            <th>@lang('b2b::app.shop.customers.account.quotes.view.negotiated-price')</th>
                                            <th>@lang('b2b::app.shop.customers.account.quotes.view.quantity')</th>
                                            <th style="text-align: right;">@lang('b2b::app.shop.customers.account.quotes.view.sub-total')</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr v-for="quotation in msg.quotations" :key="quotation.id">
                                            <td>
                                                @{{ quotation.name }}

                                                <div class="text-[11px] italic text-zinc-500" v-if="quotation.sku">@{{ quotation.sku }}</div>

                                                <div v-if="getAttributes(quotation.item.additional)" class="mt-0.5">
                                                    <div
                                                        v-for="(attribute, key) in getAttributes(quotation.item.additional)"
                                                        :key="key"
                                                        class="text-[11px] text-zinc-500"
                                                    >
                                                        <b>@{{ attribute.attribute_name }}:</b> @{{ attribute.option_label }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>@{{ quotation.item ? formatCurrency(quotation.item.price) : '—' }}</td>
                                            <td>
                                                <span v-if="discountLabel(quotation)" class="font-medium text-green-700">@{{ discountLabel(quotation) }}</span>
                                                <span v-else class="text-zinc-400">—</span>
                                            </td>
                                            <td class="font-semibold">@{{ formatCurrency(lineNegotiatedPrice(quotation)) }}</td>
                                            <td>@{{ quotation.qty }}</td>
                                            <td style="text-align: right;" class="font-semibold">@{{ formatCurrency(lineSubTotal(quotation)) }}</td>
                                        </tr>
                                    </tbody>

                                    <tfoot>
                                        <tr>
                                            <td colspan="5" style="text-align: right;" class="font-semibold">@lang('b2b::app.shop.customers.account.quotes.view.sub-total')</td>
                                            <td style="text-align: right;" class="font-semibold">@{{ formatCurrency(offerSubTotal(msg)) }}</td>
                                        </tr>

                                        <tr v-if="offerDiscountOnTotal(msg) > 0.0001">
                                            <td colspan="5" style="text-align: right;" class="font-semibold">@lang('b2b::app.shop.customers.account.quotes.view.discount-on-total')</td>
                                            <td style="text-align: right;" class="font-semibold text-green-700">− @{{ formatCurrency(offerDiscountOnTotal(msg)) }}</td>
                                        </tr>

                                        <tr>
                                            <td colspan="5" style="text-align: right;" class="font-bold">@lang('b2b::app.shop.customers.account.quotes.view.negotiated-total')</td>
                                            <td style="text-align: right;" class="font-bold">@{{ formatCurrency(offerNegotiatedTotal(msg)) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Status -->
                            <span
                                v-if="msg.status"
                                class="b2b-chat-status"
                                :class="msg.status === 'Rejected' ? 'is-rejected' : 'is-ok'"
                                v-text="msg.status"
                            ></span>
                        </div>
                    </div>
                </div>

                <!-- Empty -->
                <div
                    v-if="(!messages.data || messages.data.length === 0) && (!messages.length || messages.length === 0)"
                    class="py-10 text-center text-sm text-zinc-500"
                >
                    <template v-if="hasActiveFilters">
                        <p class="mb-2">@lang('b2b::app.shop.customers.account.quotes.view.no-messages-filtered')</p>
                        <button
                            @click="clearFilters"
                            type="button"
                            class="text-navyBlue underline"
                        >
                            @lang('b2b::app.shop.customers.account.quotes.view.clear-filters')
                        </button>
                    </template>

                    <template v-else>
                        @lang('b2b::app.shop.customers.account.quotes.view.no-messages')
                    </template>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="messages.prev_page_url || messages.next_page_url" class="mt-4 flex items-center justify-center gap-2 text-sm">
                <button
                    v-if="messages.prev_page_url"
                    @click="loadPage(messages.prev_page_url)"
                    :disabled="loading"
                    type="button"
                    class="rounded-lg border px-4 py-2 hover:bg-zinc-50 disabled:opacity-50"
                >
                    @lang('b2b::app.shop.customers.account.quotes.view.previous')
                </button>

                <span class="text-zinc-500" v-if="messages.current_page">
                    @{{ messages.current_page }} / @{{ messages.last_page }}
                </span>

                <button
                    v-if="messages.next_page_url"
                    @click="loadPage(messages.next_page_url)"
                    :disabled="loading"
                    type="button"
                    class="rounded-lg border px-4 py-2 hover:bg-zinc-50 disabled:opacity-50"
                >
                    @lang('b2b::app.shop.customers.account.quotes.view.next')
                </button>
            </div>

            <!-- Composer -->
            <div v-if="canSendMessage" class="b2b-chat-composer">
                <textarea
                    v-model="newMessage"
                    rows="1"
                    @keydown.enter.exact.prevent="sendMessage"
                    :placeholder="'@lang('b2b::app.shop.customers.account.quotes.view.message-placeholder')'"
                    class="b2b-chat-input"
                ></textarea>

                <button
                    type="button"
                    class="b2b-chat-send"
                    :disabled="sending || ! newMessage.trim()"
                    @click="sendMessage"
                    aria-label="@lang('b2b::app.shop.customers.account.quotes.view.btn-send')"
                >
                    <span class="icon-arrow-right text-xl rtl:icon-arrow-left"></span>
                </button>
            </div>
        </div>
    </script>

    <script type="module">
        app.component('quote-messages', {
            template: '#quote-messages-template',

            props: {
                initialQuote: {
                    type: Object,
                    required: true,
                },

                canSendMessage: {
                    type: Boolean,
                    default: false,
                },
            },

            data() {
                return {
                    quote: this.initialQuote,
                    messages: [],
                    messageUrl: '{{ route('shop.customers.account.quotes.messages', $quote->id) }}',
                    sendUrl: '{{ route('shop.customers.account.quotes.send_message', $quote->id) }}',
                    loading: false,
                    sending: false,
                    newMessage: '',
                    filters: {
                        has_quotations: '',
                        user_type: '',
                    },
                };
            },

            created() {
                this.quote = this.initialQuote;
                this.loadPage(this.messageUrl);
            },

            computed: {
                hasActiveFilters() {
                    return this.filters.has_quotations !== '' || this.filters.user_type !== '';
                },
            },

            methods: {
                formatDate(date) {
                    if (! date) return '';

                    return new Date(date).toLocaleString();
                },

                formatCurrency(amount) {
                    if (! amount) return '';

                    return (this.$shop && this.$shop.formatPrice)
                        ? this.$shop.formatPrice(amount)
                        : amount;
                },

                /**
                 * The item-level discount stored on the offer snapshot ("10%" or a fixed
                 * amount). Null when the line has no item discount.
                 */
                discountLabel(quotation) {
                    const value = parseFloat(quotation.discount_value);

                    if (! quotation.discount_type || ! (value > 0)) {
                        return null;
                    }

                    return quotation.discount_type === 'percent'
                        ? parseFloat(value.toFixed(2)) + '%'
                        : this.formatCurrency(value);
                },

                /**
                 * Per-unit price after the ITEM discount only (before any whole-quote discount),
                 * mirroring the quote items table's "Negotiated Price" column.
                 */
                lineNegotiatedPrice(quotation) {
                    const original = quotation.item ? parseFloat(quotation.item.price) : parseFloat(quotation.price);
                    const value = parseFloat(quotation.discount_value);

                    if (! quotation.discount_type || ! (value > 0)) {
                        return original;
                    }

                    if (quotation.discount_type === 'percent') {
                        return Math.max(0, original - (original * Math.min(value, 100) / 100));
                    }

                    return Math.max(0, original - value);
                },

                lineSubTotal(quotation) {
                    return this.lineNegotiatedPrice(quotation) * (parseInt(quotation.qty) || 0);
                },

                offerSubTotal(msg) {
                    return (msg.quotations || []).reduce((sum, q) => sum + this.lineSubTotal(q), 0);
                },

                // Whole-quote ("Discount on Total"): the snapshot price already folds it in.
                offerNegotiatedTotal(msg) {
                    return (msg.quotations || []).reduce((sum, q) => sum + (parseFloat(q.price) * (parseInt(q.qty) || 0)), 0);
                },

                offerDiscountOnTotal(msg) {
                    return this.offerSubTotal(msg) - this.offerNegotiatedTotal(msg);
                },

                getUserTypeLabel(userType) {
                    const labels = {
                        customer: '@lang('b2b::app.shop.customers.account.quotes.view.you')',
                        agent: '@lang('b2b::app.shop.customers.account.quotes.view.seller')',
                        admin: '@lang('b2b::app.shop.customers.account.quotes.view.seller')',
                    };

                    return labels[userType] || labels.customer;
                },

                getInitials(userType) {
                    return (this.getUserTypeLabel(userType) || '?').charAt(0).toUpperCase();
                },

                getAttributes(additional) {
                    try {
                        const data = JSON.parse(additional);

                        return data && data.attributes ? data.attributes : null;
                    } catch (e) {
                        return null;
                    }
                },

                async sendMessage() {
                    const message = this.newMessage.trim();

                    if (! message || this.sending) return;

                    this.sending = true;

                    try {
                        const response = await fetch(this.sendUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ message }),
                        });

                        if (response.ok) {
                            this.newMessage = '';
                            await this.loadPage(this.messageUrl);
                        } else {
                            const data = await response.json().catch(() => ({}));

                            this.$emitter.emit('add-flash', {
                                type: 'error',
                                message: data.message || '@lang('b2b::app.shop.customers.account.quotes.view.error-message')',
                            });
                        }
                    } catch (error) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: '@lang('b2b::app.shop.customers.account.quotes.view.error-message')',
                        });
                    } finally {
                        this.sending = false;
                    }
                },

                async loadPage(url) {
                    this.loading = true;

                    try {
                        const urlObj = new URL(url, window.location.origin);

                        if (this.filters.has_quotations) {
                            urlObj.searchParams.set('has_quotations', this.filters.has_quotations);
                        }

                        if (this.filters.user_type) {
                            urlObj.searchParams.set('user_type', this.filters.user_type);
                        }

                        const response = await fetch(urlObj.toString(), {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (response.ok) {
                            this.messages = await response.json();

                            this.$nextTick(() => {
                                const panel = document.getElementById('messages-panel');

                                if (panel) panel.scrollTop = panel.scrollHeight;
                            });
                        }
                    } catch (error) {
                        //
                    } finally {
                        this.loading = false;
                    }
                },

                applyFilters() {
                    this.loadPage(this.messageUrl);
                },

                clearFilters() {
                    this.filters.has_quotations = '';
                    this.filters.user_type = '';
                    this.applyFilters();
                },
            },
        });
    </script>
@endPushOnce
