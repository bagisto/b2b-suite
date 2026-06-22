<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.quotes.view.title' , ['id' => $quote->quotation_number])
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="quotes.view" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="min-w-0 flex-auto">
        {!! view_render_event('bagisto.shop.customers.account.quote.view.before', ['quote' => $quote]) !!}

        @php
            $isDraft = $quote->state === 'quotation' && $quote->status === 'draft';
            $isDraftOrCompletedStatus = in_array($quote->status, ['draft', 'purchase_order', 'expired', 'completed', 'accepted', 'rejected', 'ordered']);
            $isOpenOrNegotiation = in_array($quote->status, ['open', 'negotiation']);
            $isOrderedOrRejected = in_array($quote->status, ['draft', 'ordered', 'completed', 'rejected']);
        @endphp

        <!--
            Page header with all quote actions grouped at the top. The action modals are
            each their own form, so they live outside the update form; the Save button
            submits the update form via the HTML `form` attribute association.
        -->
        <div class="grid gap-4">
            <!-- Title row -->
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <h2 class="text-2xl font-medium ltr:ml-2.5 rtl:mr-2.5 max-md:text-xl max-sm:text-base md:ltr:ml-0 md:rtl:mr-0">
                    @lang('b2b::app.shop.customers.account.quotes.view.title' , ['id' => $quote->quotation_number])
                </h2>

                <!-- Back Button -->
                <a
                    href="{{ route('shop.customers.account.quotes.index') }}"
                    class="transparent-button px-5 py-2.5"
                >
                    @lang('b2b::app.shop.customers.account.quotes.view.btn-back')
                </a>
            </div>

            <!-- Actions row -->
            <div class="flex flex-wrap items-center justify-end gap-2.5 max-md:w-full">
                @if ($quote->order_id)
                    <!-- View the placed order -->
                    <a
                        href="{{ route('shop.customers.account.orders.view', $quote->order_id) }}"
                        class="primary-button rounded-lg px-8 py-3 text-center max-md:py-2.5"
                    >
                        @lang('b2b::app.shop.customers.account.quotes.view.btn-view')
                    </a>
                @endif

                @if ($isDraft)
                    <!-- Submit Quote (name + description + per-item discount editor) -->
                    @include('b2b::shop.customers.account.quotes.view.submission-modal', [
                        'quote'       => $quote,
                        'mode'        => 'draft',
                        'buttonLabel' => trans('b2b::app.shop.customers.account.quotes.view.btn-submit-quote'),
                        'buttonClass' => 'primary-button',
                    ])

                    <!-- Delete Quote -->
                    @include('b2b::shop.customers.account.quotes.view.partials.modal', [
                        'action'           => 'delete',
                        'buttonText'       => 'btn-delete-quote',
                        'buttonClass'      => 'secondary-button',
                        'actionButtonText' => 'btn-delete',
                    ])
                @endif

                @if (! $isAdminLastQuotation && $quote->state == 'quotation' && $isOpenOrNegotiation)
                    <!-- Re-submit / counter-offer (per-item discount editor + message) -->
                    @include('b2b::shop.customers.account.quotes.view.submission-modal', [
                        'quote'       => $quote,
                        'mode'        => 'counter',
                        'buttonLabel' => trans('b2b::app.shop.customers.account.quotes.view.btn-again-quote'),
                        'buttonClass' => 'secondary-button',
                    ])
                @endif

                @if (! $isOrderedOrRejected)
                    <!-- Reject Quote -->
                    @include('b2b::shop.customers.account.quotes.view.partials.modal', [
                        'action'      => 'reject',
                        'buttonText'  => 'btn-reject-quote',
                        'buttonClass' => 'secondary-button',
                    ])
                @endif
            </div>
        </div>

        <!-- Quote Information -->
        @include('b2b::shop.customers.account.quotes.view.index', ['quote' => $quote])

        <!-- Quote Items -->
        @include('b2b::shop.customers.account.quotes.view.items', ['quote' => $quote, 'isAdminLastQuotation' => $isAdminLastQuotation])

        <!-- Quote Attachments -->
        @include('b2b::shop.customers.account.quotes.view.attachments', ['quote' => $quote])

        <!-- Quote Messages (Only when the member may use the quote chat.) -->
        @if (customer_bouncer()->hasPermission('account.quotes.messages'))
            @include('b2b::shop.customers.account.quotes.view.messages', ['quote' => $quote, 'isAdminLastQuotation' => $isAdminLastQuotation])
        @endif

        {!! view_render_event('bagisto.shop.customers.account.quote.view.after', ['quote' => $quote]) !!}
    </div>
</x-shop::layouts.account>
