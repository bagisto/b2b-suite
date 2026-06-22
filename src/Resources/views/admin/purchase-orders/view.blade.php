<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.purchase-orders.view.title', ['id' => $quote->po_number])
    </x-slot>

    <!-- Header -->
    <div class="grid">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            {!! view_render_event('bagisto.admin.b2b.quote.title.before', ['quote' => $quote]) !!}

            <div class="flex items-center gap-2.5">
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                    @lang('b2b::app.admin.purchase-orders.view.title', ['id' => $quote->po_number])
                </p>

                <!-- Order Status -->
                <span class="{{ \Webkul\B2BSuite\Models\CustomerQuote::statusLabelClass($quote->status) }} mx-1.5 text-sm">
                    @lang("b2b::app.admin.quotes.view.$quote->status")
                </span>
            </div>

            {!! view_render_event('bagisto.admin.b2b.quote.title.after', ['quote' => $quote]) !!}

            <div class="flex items-center gap-x-2.5">
                <!-- Order View Button -->
                <a
                    href="{{ route('admin.sales.orders.view', $quote->order_id) }}"
                    class="primary-button"
                >
                    @lang('b2b::app.admin.quotes.view.view-btn')
                </a>
                
                <!-- Back Button -->
                <a
                    href="{{ route('admin.b2b.purchase_orders.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b::app.admin.quotes.view.back-btn')
                </a>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <!-- PO details: Information + Company side by side; stacks to one column below 1280px. -->
        <div class="b2b-view-columns mt-3.5">
            <!-- Left Component -->
            <div class="flex flex-col gap-2">
                {!! view_render_event('bagisto.admin.b2b.quote.left_component.before', ['quote' => $quote]) !!}

                <!-- Quote Information -->
                @include('b2b::admin.quotes.view.quote-information')
            </div>

            <!-- Right Component -->
            <div class="flex flex-col gap-2">
                {!! view_render_event('bagisto.admin.sales.order.right_component.before', ['quote' => $quote]) !!}

                <!-- Company Information -->
                @include('b2b::admin.quotes.view.company-information')
            </div>
        </div>

        <!-- Order Items (full-width row) -->
        <div class="mt-2 flex flex-col gap-2">
            @include('b2b::admin.quotes.view.items')
        </div>

        <!-- Order Attachments (full-width row) -->
        <div class="mt-2 flex flex-col gap-2">
            @include('b2b::admin.quotes.view.attachments')
        </div>

        <!-- Quote Messages -->
        @include('b2b::admin.quotes.view.messages', ['quote' => $quote])
    </div>

    @pushOnce('styles')
        <style>
            /**
             * Standardised two-column layout shared by the quote and purchase-order view
             * pages: a single stacked column on smaller screens and a main + fixed side
             * column from 1280px up. Defined here because the responsive width variants are
             * purged out of the B2B admin bundle.
             */
            .b2b-view-columns {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
                gap: 0.625rem;
            }

            @media (min-width: 1280px) {
                .b2b-view-columns {
                    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                    align-items: start;
                }
            }
        </style>
    @endPushOnce
</x-admin::layouts>