<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.quotes.view.title', ['id' => $quote->quotation_number])
    </x-slot>

    <!-- Header -->
    <div class="grid">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            {!! view_render_event('bagisto.admin.b2b.quote.title.before', ['quote' => $quote]) !!}

            <div class="flex items-center gap-2.5">
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                    @lang('b2b::app.admin.quotes.view.title', ['id' => $quote->quotation_number])
                </p>

                <!-- Order Status -->
                <span class="{{ \Webkul\B2BSuite\Models\CustomerQuote::statusLabelClass($quote->status) }} mx-1.5 text-sm">
                    @lang("b2b::app.admin.quotes.view.$quote->status")
                </span>
            </div>

            {!! view_render_event('bagisto.admin.b2b.quote.title.after', ['quote' => $quote]) !!}

            <div class="flex items-center gap-x-2.5">
                @if ($quote->order_id)
                    <!-- View the placed order -->
                    <a
                        href="{{ route('admin.sales.orders.view', $quote->order_id) }}"
                        class="primary-button"
                    >
                        @lang('b2b::app.admin.quotes.view.view-btn')
                    </a>
                @endif

                <!-- Back Button -->
                <a
                    href="{{ route('admin.b2b.quotes.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b::app.admin.quotes.view.back-btn')
                </a>
            </div>
        </div>
    </div>

    @php
        // "accepted" is included so the admin can still send a revised offer after a
        // quotation has been sent/accepted (until it is ordered, completed or rejected).
        $canSendQuotation = in_array($quote->status, ['open', 'negotiation', 'rejected', 'accepted']);
        $isOrderedOrCompletedOrRejected = in_array($quote->status, ['ordered', 'completed', 'rejected']);

        // The admin can accept the buyer's offer while it is the buyer's turn (the buyer
        // submitted or countered last), i.e. the admin has not made the latest quotation.
        $canAdminAccept = in_array($quote->status, ['open', 'negotiation'])
            && ! ($adminIsLastQuotation ?? false);

        // A quote can be rejected by either side; the rejection is the last message, so its
        // author tells us who declined (drives which note the admin sees).
        $rejectedByAdmin = $quote->status === 'rejected'
            && optional($quote->messages()->orderByDesc('id')->first())->user_type === 'admin';
    @endphp

    <!-- Status note: the customer has accepted / rejected; the admin can still revise. -->
    @if ($quote->status === 'accepted')
        <div
            class="mt-4 flex items-start gap-2 rounded-lg p-3"
            style="background-color: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25);"
        >
            <span class="icon-information shrink-0 text-xl" style="color: #16a34a;"></span>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                @lang('b2b::app.admin.quotes.view.customer-accepted-note')
            </p>
        </div>
    @elseif ($quote->status === 'rejected')
        <div
            class="mt-4 flex items-start gap-2 rounded-lg p-3"
            style="background-color: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);"
        >
            <span class="icon-information shrink-0 text-xl" style="color: #f59e0b;"></span>
            <p class="text-sm text-gray-700 dark:text-gray-200">
                @lang('b2b::app.admin.quotes.view.'.($rejectedByAdmin ? 'admin-rejected-note' : 'customer-rejected-note'))
            </p>
        </div>
    @endif

    <!-- Quote Actions -->
    <div class="mt-4 flex flex-wrap items-center justify-end gap-2.5">
        @if (! $isOrderedOrCompletedOrRejected)
            <!-- Reject Quotation (kept less prominent than the primary action) -->
            @include('b2b::admin.quotes.view.partials.modal', [
                'action'      => 'reject',
                'buttonText'  => 'btn-reject-quote',
                'buttonClass' => 'secondary-button',
            ])
        @endif

        @if ($canAdminAccept)
            <!-- Accept the buyer's quotation -->
            @include('b2b::admin.quotes.view.partials.modal', [
                'action'           => 'accept',
                'buttonText'       => 'btn-accept-quote',
                'buttonClass'      => 'secondary-button',
                'actionButtonText' => 'btn-accept',
            ])
        @endif

        @if ($canSendQuotation)
            <!-- Send Quotation (per-item & whole-quote discounts) — primary, on the right -->
            @include('b2b::admin.quotes.view.negotiation')
        @endif
    </div>

    <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
        <!-- Quote details: Information + Company side by side -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                {!! view_render_event('bagisto.admin.b2b.quote.left_component.before', ['quote' => $quote]) !!}

                <!-- Quote Information -->
                @include('b2b::admin.quotes.view.quote-information')
            </div>

            <!-- Right Component -->
            <div class="flex w-[525px] flex-col gap-2 max-sm:w-full">
                {!! view_render_event('bagisto.admin.sales.order.right_component.before', ['quote' => $quote]) !!}

                <!-- Company Information -->
                @include('b2b::admin.quotes.view.company-information')
            </div>
        </div>

        <!-- Quote Items (full-width row) -->
        <div class="mt-2 flex flex-col gap-2">
            @include('b2b::admin.quotes.view.items')
        </div>

        <!-- Quote Attachments (full-width row) -->
        <div class="mt-2 flex flex-col gap-2">
            @include('b2b::admin.quotes.view.attachments')
        </div>
                
        <!-- Quote Messages -->
        @include('b2b::admin.quotes.view.messages', ['quote' => $quote])
    </div>
</x-admin::layouts>