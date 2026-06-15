@php
    $canSendMessage = in_array($quote->status, ['open', 'negotiation']);
@endphp

<quote-messages
    :initial-quote="{{ json_encode($quote) }}"
    :can-send-message="{{ $canSendMessage ? 'true' : 'false' }}"
></quote-messages>

@push('styles')
    <style>
        .b2b-chat-thread { display: flex; flex-direction: column; gap: 1.25rem; max-height: 440px; overflow-y: auto; padding: 1rem; }
        .b2b-chat-row { display: flex; align-items: flex-end; gap: 0.625rem; }
        .b2b-chat-row.is-mine { flex-direction: row-reverse; }
        .b2b-chat-avatar { height: 2.25rem; width: 2.25rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; background: #eef0f7; color: #1f2937; }
        .b2b-chat-row.is-mine .b2b-chat-avatar { background: #0f172a; color: #fff; }
        .b2b-chat-col { max-width: 76%; display: flex; flex-direction: column; }
        .b2b-chat-row.is-mine .b2b-chat-col { align-items: flex-end; }
        .b2b-chat-meta { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem; font-size: 0.6875rem; color: #9ca3af; }
        .b2b-chat-meta b { color: #374151; font-weight: 600; }
        .b2b-chat-bubble { border-radius: 1rem; padding: 0.625rem 0.875rem; font-size: 0.875rem; line-height: 1.45; word-break: break-word; }
        .b2b-chat-bubble.is-mine { background: #0f172a; color: #fff; border-bottom-right-radius: 0.25rem; }
        .b2b-chat-bubble.is-theirs { background: #f3f4f6; color: #1f2937; border-bottom-left-radius: 0.25rem; }
        .b2b-chat-text { white-space: pre-line; }
        .b2b-chat-quotations { margin-top: 0.5rem; border-radius: 0.625rem; background: #fff; color: #1f2937; overflow: hidden; border: 1px solid #e5e7eb; }
        .b2b-chat-quotations table { width: 100%; border-collapse: collapse; font-size: 0.75rem; }
        .b2b-chat-quotations th { background: #f9fafb; text-align: left; padding: 0.5rem 0.625rem; font-weight: 600; }
        .b2b-chat-quotations td { padding: 0.5rem 0.625rem; border-top: 1px solid #f1f1f4; }
        .b2b-chat-status { display: inline-block; margin-top: 0.5rem; border-radius: 9999px; padding: 0.125rem 0.625rem; font-size: 0.6875rem; font-weight: 600; }
        .b2b-chat-status.is-rejected { background: #fde8e6; color: #b42318; }
        .b2b-chat-status.is-ok { background: #e7f6ea; color: #1f7a33; }
        .b2b-chat-composer { display: flex; align-items: flex-end; gap: 0.5rem; padding: 1rem; border-top: 1px solid #f1f1f4; }
        .b2b-chat-input { flex: 1; resize: none; border: 1px solid #d1d5db; border-radius: 1rem; padding: 0.75rem 1rem; font-size: 0.875rem; outline: none; transition: border-color .2s; max-height: 140px; background: transparent; }
        .b2b-chat-input:focus { border-color: #0f172a; }
        .b2b-chat-send { height: 2.75rem; width: 2.75rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: #0f172a; color: #fff; cursor: pointer; transition: opacity .2s; }
        .b2b-chat-send:disabled { opacity: .45; cursor: not-allowed; }

        /* Dark mode */
        .dark .b2b-chat-avatar { background: #1f2937; color: #e5e7eb; }
        .dark .b2b-chat-row.is-mine .b2b-chat-avatar { background: #3b82f6; color: #fff; }
        .dark .b2b-chat-meta { color: #6b7280; }
        .dark .b2b-chat-meta b { color: #d1d5db; }
        .dark .b2b-chat-bubble.is-mine { background: #2563eb; color: #fff; }
        .dark .b2b-chat-bubble.is-theirs { background: #1f2937; color: #e5e7eb; }
        .dark .b2b-chat-quotations { background: #111827; color: #e5e7eb; border-color: #374151; }
        .dark .b2b-chat-quotations th { background: #1f2937; }
        .dark .b2b-chat-quotations td { border-color: #374151; }
        .dark .b2b-chat-input { border-color: #374151; color: #e5e7eb; }
        .dark .b2b-chat-input:focus { border-color: #3b82f6; }
        .dark .b2b-chat-composer, .dark .b2b-chat-thread { border-color: #1f2937; }
    </style>
@endpush

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="quote-messages-template"
    >
        <div class="box-shadow mt-4 rounded bg-white dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between gap-3 p-4 max-sm:flex-col max-sm:items-start">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('b2b::app.admin.quotes.view.quote-messages')
                </p>

                <!-- Filters -->
                <div class="flex items-center gap-2 max-sm:flex-wrap">
                    <select
                        v-model="filters.has_quotations"
                        @change="applyFilters"
                        class="rounded-lg border px-3 py-2 text-xs dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">All messages</option>
                        <option value="true">With quotations</option>
                    </select>

                    <select
                        v-model="filters.user_type"
                        @change="applyFilters"
                        class="rounded-lg border px-3 py-2 text-xs dark:border-gray-800 dark:bg-gray-900 dark:text-white"
                    >
                        <option value="">Everyone</option>
                        <option value="customer">@{{ customerName }}</option>
                        <option value="admin">You</option>
                    </select>

                    <button
                        @click="clearFilters"
                        v-if="hasActiveFilters"
                        type="button"
                        class="text-xs font-medium text-blue-600 underline"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex justify-center py-6 text-sm text-gray-500 dark:text-white">
                Loading messages...
            </div>

            <!-- Thread -->
            <div v-else id="messages-panel" class="b2b-chat-thread">
                <div
                    v-for="msg in (messages.data ? messages.data : messages)"
                    :key="msg.id"
                    class="b2b-chat-row"
                    :class="{ 'is-mine': msg.user_type !== 'customer' }"
                >
                    <div class="b2b-chat-avatar">@{{ getInitials(msg.user_type) }}</div>

                    <div class="b2b-chat-col">
                        <div class="b2b-chat-meta">
                            <b>@{{ getUserTypeLabel(msg.user_type) }}</b>
                            <span>@{{ formatDate(msg.created_at) }}</span>
                        </div>

                        <div
                            class="b2b-chat-bubble"
                            :class="msg.user_type !== 'customer' ? 'is-mine' : 'is-theirs'"
                        >
                            <p class="b2b-chat-text" v-if="msg.message">@{{ msg.message }}</p>

                            <!-- Quotations -->
                            <div v-if="msg.quotations && msg.quotations.length > 0" class="b2b-chat-quotations">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>@lang('b2b::app.admin.quotes.view.name')</th>
                                            <th>@lang('b2b::app.admin.quotes.view.price')</th>
                                            <th>@lang('b2b::app.admin.quotes.view.quantity')</th>
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
                                            <td>@{{ formatCurrency(quotation.price) }}</td>
                                            <td>@{{ quotation.qty }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

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
                    class="py-10 text-center text-sm text-gray-500 dark:text-white"
                >
                    <template v-if="hasActiveFilters">
                        <p class="mb-2">No messages match the current filters.</p>
                        <button @click="clearFilters" type="button" class="text-blue-600 underline">Clear filters</button>
                    </template>

                    <template v-else>
                        No messages yet.
                    </template>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="messages.prev_page_url || messages.next_page_url" class="flex items-center justify-center gap-2 px-4 pb-2 text-sm">
                <button
                    v-if="messages.prev_page_url"
                    @click="loadPage(messages.prev_page_url)"
                    :disabled="loading"
                    type="button"
                    class="rounded-lg border px-4 py-2 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-800"
                >
                    Previous
                </button>

                <span class="text-gray-500 dark:text-white" v-if="messages.current_page">
                    @{{ messages.current_page }} / @{{ messages.last_page }}
                </span>

                <button
                    v-if="messages.next_page_url"
                    @click="loadPage(messages.next_page_url)"
                    :disabled="loading"
                    type="button"
                    class="rounded-lg border px-4 py-2 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-800"
                >
                    Next
                </button>
            </div>

            <!-- Composer -->
            <div v-if="canSendMessage" class="b2b-chat-composer">
                <textarea
                    v-model="newMessage"
                    rows="1"
                    @keydown.enter.exact.prevent="sendMessage"
                    placeholder="@lang('b2b::app.admin.quotes.view.message-placeholder')"
                    class="b2b-chat-input dark:text-white"
                ></textarea>

                <button
                    type="button"
                    class="b2b-chat-send"
                    :disabled="sending || ! newMessage.trim()"
                    @click="sendMessage"
                    aria-label="@lang('b2b::app.admin.quotes.view.btn-send')"
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
                    customerName: '{{ $quote->customer->name }}',
                    messages: [],
                    messageUrl: '{{ route('admin.b2b.quotes.messages', $quote->id) }}',
                    sendUrl: '{{ route('admin.b2b.quotes.send_message', $quote->id) }}',
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

                    return (this.$admin && this.$admin.formatPrice)
                        ? this.$admin.formatPrice(amount)
                        : amount;
                },

                getUserTypeLabel(userType) {
                    if (userType === 'customer') {
                        return this.customerName || 'Customer';
                    }

                    return 'You';
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
                        }
                    } catch (error) {
                        //
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
