<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.purchase-orders.view.title' , ['id' => $quote->po_number])
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="profile.edit" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="flex-auto">
    
        {!! view_render_event('bagisto.shop.customers.account.purchase_order.view.before', ['quote' => $quote]) !!}

        <!-- Page Header -->
        <div class="grid gap-2.5">
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <h2 class="text-2xl font-medium ltr:ml-2.5 rtl:mr-2.5 max-md:text-xl max-sm:text-base md:ltr:ml-0 md:rtl:mr-0">
                    @lang('b2b_suite::app.shop.customers.account.purchase-orders.view.title' , ['id' => $quote->po_number])
                </h2>

                <div class="flex items-center gap-x-2.5">
                    <!-- Back Button -->
                    <a
                        href="{{ route('shop.customers.account.purchase_orders.index') }}"
                        class="transparent-button px-5 py-2.5"
                    >
                        @lang('b2b_suite::app.shop.customers.account.quotes.view.btn-back')
                    </a>
                    
                    <!-- Order View Button -->
                    <a
                        href="{{ route('shop.customers.account.orders.view', $quote->order_id) }}"
                        class="secondary-button px-5 py-2.5"
                    >
                        @lang('b2b_suite::app.shop.customers.account.quotes.view.btn-view')
                    </a>
                </div>
            </div>
        </div>
            
        <!-- Quote Information -->
        @include('b2b_suite::shop.customers.account.quotes.view.index', ['quote' => $quote])
        
        <!-- Quote Items -->
        @include('b2b_suite::shop.customers.account.quotes.view.items', ['quote' => $quote])

        <!-- Quote Attachments -->
        @include('b2b_suite::shop.customers.account.quotes.view.attachments', ['quote' => $quote])
        
        <!-- Quote Messages -->
        @include('b2b_suite::shop.customers.account.quotes.view.messages', ['quote' => $quote, 'isAdminLastQuotation' => $isAdminLastQuotation])

        {!! view_render_event('bagisto.shop.customers.account.purchase_order.view.after', ['quote' => $quote]) !!}
    
    </div>
</x-shop::layouts.account>
