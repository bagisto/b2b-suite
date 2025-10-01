<quote-messages 
    :initial-quote="{{ json_encode($quote) }}"
></quote-messages>

<!-- Action Buttons -->
<div class="mt-4 flex justify-end gap-x-2.5">
    @php
        $isNegotiation = $quote->state === 'quotation' && $quote->status === 'negotiation';
        $isOpenOrNegotiationOrRejected = in_array($quote->status, ['open', 'negotiation', 'rejected']);
        $isOrderedOrCompletedOrRejected = in_array($quote->status, ['ordered', 'completed', 'rejected']);
    @endphp
    
    <!-- Send Message modal -->
    @include('b2b_suite::admin.quotes.view.partials.modal', [
        'buttonText'       => 'btn-message',
        'actionButtonText' => 'btn-send'
    ])

    @if ($isNegotiation)
        <!-- Accept Quote Modal -->
        @include('b2b_suite::admin.quotes.view.partials.modal', [
            'action'     => 'accept',
            'buttonText' => 'btn-accept-quote'
        ])
    @endif

    @if ($isOpenOrNegotiationOrRejected)
        <!-- Submit Quote modal -->
        @include('b2b_suite::admin.quotes.view.partials.modal', [
            'action'     => 'submit',
            'buttonText' => 'btn-submit-quote'
        ])
    @endif

    @if (! $isOrderedOrCompletedOrRejected)
        <!-- Reject Quote modal -->
        @include('b2b_suite::admin.quotes.view.partials.modal', [
            'action'     => 'reject',
            'buttonText' => 'btn-reject-quote'
        ])
    @endif
</div>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="quote-messages-template"
    >
        <div class="box-shadow mt-4 rounded bg-white dark:bg-gray-900">
            <div class="flex justify-between p-4">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('b2b_suite::app.admin.quotes.view.quote-messages')
                </p>
                
                <!-- Filter Controls -->
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-base text-gray-800 dark:text-white">Filter:</label>
                        <select 
                            v-model="filters.has_quotations" 
                            @change="applyFilters"
                            class="rounded border px-3 py-2.5 text-sm dark:border-gray-800"
                        >
                            <option value="">All Messages</option>
                            <option value="true">With Quotations</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <label class="text-base text-gray-800 dark:text-white">User:</label>
                        <select 
                            v-model="filters.user_type" 
                            @change="applyFilters"
                            class="rounded border px-3 py-2.5 text-sm dark:border-gray-800"
                        >
                            <option value="">All Users</option>
                            <option value="customer">@{{ this.customerName }}</option>
                            <option value="system">You</option>
                        </select>
                    </div>
                    
                    <button 
                        @click="clearFilters"
                        v-if="hasActiveFilters"
                        class="text-xs text-blue-600 underline hover:text-blue-800"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="loading" class="flex justify-center py-4">
                <div class="text-gray-800 dark:text-white">Loading messages...</div>
            </div>

            <!-- Messages List -->
            <div 
                v-else
                id="messages-panel"
                class="flex max-h-[350px] flex-col gap-4 overflow-y-auto p-4"
            >
                <div 
                    v-for="msg in (messages.data ? messages.data : messages)" 
                    :key="msg.id"
                    :class="['flex', msg.user_type !== 'customer' ? 'justify-end' : 'justify-start']"
                >
                    <div 
                        :class="['rounded-lg px-4 py-2 max-w-[70%] border dark:border-gray-800 dark:bg-gray-800', msg.user_type !== 'customer' ? 'bg-gray-300 text-right' : 'bg-gray-100 text-left']"
                    >
                        <!-- User info -->
                        <div class="mb-1 flex items-center gap-2">
                            <span :class="[
                                'text-xs font-medium',
                                msg.user_type === 'customer' ? 'text-blue-600' : 'text-red-600'
                            ]">
                                @{{ getUserTypeLabel(msg.user_type) }}
                            </span>
                        </div>

                        <!-- Message quotations if any -->
                        <div v-if="msg.quotations && msg.quotations.length > 0" class="mt-2">
                            <div class="text-md mb-1 text-left font-bold text-gray-600 dark:text-white">Quotations:</div>
                            
                            <table class="w-full border text-left text-sm dark:border-gray-800">
                                <thead class="bg-gray-200 dark:bg-gray-900">
                                    <tr>
                                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                            @lang('b2b_suite::app.admin.quotes.view.name')
                                        </th>
                                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                            @lang('b2b_suite::app.admin.quotes.view.price')
                                        </th>
                                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                            @lang('b2b_suite::app.admin.quotes.view.quantity')
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr 
                                        v-for="quotation in msg.quotations" 
                                        :key="quotation.id"
                                        class="border-b bg-white dark:border-gray-800 dark:bg-gray-900"
                                    >   
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                            @{{ quotation.name }}

                                            <div class="text-xs italic text-zinc-500" v-if="quotation.sku">
                                                @{{ quotation.sku }}
                                            </div>
                                            
                                            <div v-if="getAttributes(quotation.item.additional)" class="mt-1">
                                                <div 
                                                    v-for="(attribute, key) in getAttributes(quotation.item.additional)" 
                                                    :key="key"
                                                    class="text-sm text-zinc-500"
                                                >
                                                    <b>@{{ attribute.attribute_name }}:</b> @{{ attribute.option_label }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                            @{{ formatCurrency(quotation.price) }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                            @{{ quotation.qty }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Message content -->
                        <div class="mb-2 mt-2 grid gap-2 text-sm text-gray-800 dark:text-white">
                            @{{ msg.message }}
                            
                            <div v-if="msg.status">
                                <span 
                                    :class="['px-2 py-1 text-normal text-white', msg.status === 'Rejected' ? 'label-canceled' : 'label-completed']" 
                                    v-text="msg.status"
                                ></span>
                            </div>
                        </div>
                        
                        <!-- Message timestamp -->
                        <div class="mt-1 text-xs text-gray-500 dark:text-white">
                            @{{ formatDate(msg.created_at) }}
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div 
                    v-if="(!messages.data || messages.data.length === 0) && (!messages.length || messages.length === 0)" 
                    class="py-8 text-center text-gray-500 dark:text-white"
                >
                    <div v-if="hasActiveFilters">
                        <p class="mb-2">No messages found matching the current filters.</p>
                        
                        <button 
                            @click="clearFilters"
                            class="text-sm text-blue-600 underline hover:text-blue-800"
                        >
                            Clear filters to see all messages
                        </button>
                    </div>
                    <div v-else>
                        No messages found for this quote.
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="messages.prev_page_url || messages.next_page_url" class="mt-4 flex items-center justify-center gap-2">
                <button 
                    v-if="messages.prev_page_url" 
                    @click="loadPage(messages.prev_page_url)" 
                    :disabled="loading"
                    class="rounded-l border bg-white px-4 py-2 hover:bg-gray-50 disabled:opacity-50"
                >
                    Previous
                </button>
                
                <span class="border-b border-t bg-gray-50 px-4 py-2" v-if="messages.current_page">
                    Page @{{ messages.current_page }} of @{{ messages.last_page }}
                </span>
                
                <button 
                    v-if="messages.next_page_url" 
                    @click="loadPage(messages.next_page_url)" 
                    :disabled="loading"
                    class="rounded-r border bg-white px-4 py-2 hover:bg-gray-50 disabled:opacity-50"
                >
                    Next
                </button>
            </div>

            <!-- Page info with filter status -->
            <div v-if="messages.total || hasActiveFilters" class="mt-2 p-4 text-center text-xs text-gray-500 dark:text-white">
                <span v-if="messages.total">
                    Showing @{{ messages.from || 0 }} to @{{ messages.to || 0 }} of @{{ messages.total }} messages
                </span>
                <span v-if="hasActiveFilters" class="ml-2 text-blue-600">
                    (Filtered
                    <span v-if="filters.has_quotations === 'true'">- With Quotations</span>
                    <span v-if="filters.user_type">- @{{ getUserTypeLabel(filters.user_type) }} Only</span>
                    )
                </span>
            </div>
        </div>
    </script>
    
    <script type="module">
        app.component('quote-messages', {
            template: '#quote-messages-template',
            
            props: {
                initialQuote: {
                    type: Object,
                    required: true
                }
            },
            
            data() {
                return {
                    quote: this.initialQuote,
                    customerName: '{{ $quote->customer->name }}',
                    messages: [],
                    messageUrl: '{{ route('admin.customers.quotes.messages', $quote->id) }}',
                    loading: false,
                    filters: {
                        has_quotations: '',
                        user_type: ''
                    }
                };
            },
            
            created() {
                this.quote = this.initialQuote;
                this.loadPage(this.messageUrl);
            },
            
            computed: {
                hasActiveFilters() {
                    return this.filters.has_quotations !== '' || this.filters.user_type !== '';
                }
            },
            
            methods: {
                formatDate(date) {
                    if (!date) return '';
                    const d = new Date(date);
                    return d.toLocaleString();
                },
                
                formatCurrency(amount) {
                    if (!amount) return '';
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD' // Change this to your currency
                    }).format(amount);
                },
                
                getUserTypeLabel(userType) {
                    console.log(this.customer);
                    const labels = {
                        'customer': this.customerName || 'Customer',
                        'agent': 'Agent',
                        'system': 'You'
                    };
                    return labels[userType] || 'You';
                },
                
                getAttributes(additional) {
                    const additionalData = JSON.parse(additional);

                    if (additionalData && additionalData.attributes) {
                        return additionalData.attributes;
                    }
                    
                    return null;
                },
                
                async loadPage(url) {
                    console.log(url);
                    this.loading = true;
                    try {
                        // Add filters to the URL
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
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.messages = data;
                            
                            // Scroll to top of messages panel
                            this.$nextTick(() => {
                                const messagesPanel = document.getElementById('messages-panel');
                                if (messagesPanel) {
                                    messagesPanel.scrollTop = 0;
                                }
                            });
                        } else {
                            console.error('Failed to load messages page');
                            // You could add user notification here
                        }
                    } catch (error) {
                        console.error('Error loading messages page:', error);
                        // You could add user notification here
                    } finally {
                        this.loading = false;
                    }
                },
                
                async applyFilters() {
                    this.loading = true;
                    try {
                        const url = this.messageUrl;
                        const params = new URLSearchParams();
                        
                        if (this.filters.has_quotations) {
                            params.append('has_quotations', this.filters.has_quotations);
                        }
                        if (this.filters.user_type) {
                            params.append('user_type', this.filters.user_type);
                        }
                        
                        const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;
                        
                        const response = await fetch(fullUrl, {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            this.messages = data;
                            
                            // Scroll to top of messages panel
                            this.$nextTick(() => {
                                const messagesPanel = document.getElementById('messages-panel');
                                if (messagesPanel) {
                                    messagesPanel.scrollTop = 0;
                                }
                            });
                        } else {
                            console.error('Failed to apply filters');
                        }
                    } catch (error) {
                        console.error('Error applying filters:', error);
                    } finally {
                        this.loading = false;
                    }
                },
                
                clearFilters() {
                    this.filters.has_quotations = '';
                    this.filters.user_type = '';
                    this.applyFilters();
                }
            }
        });
    </script>
@endPushOnce