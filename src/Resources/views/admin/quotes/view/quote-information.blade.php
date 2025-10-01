<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex justify-between p-1.5">
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @if ($quote->state == 'quotation')
                @lang('b2b_suite::app.admin.quotes.view.quote-information')
            @else
                @lang('b2b_suite::app.admin.purchase-orders.view.po-information')
            @endif
        </p>
    </div>
    
    <div class="grid grid-cols-12 gap-4 px-4 pb-4">
        <!-- Quotation Name -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.name')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->name }}
            </p>
        </div>

        <!-- Quotation Description -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.description')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->description }}
            </p>
        </div>

        <!-- Quotation Status -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.status')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                @lang("b2b_suite::app.admin.quotes.view.$quote->status")
            </p>
        </div>

        <!-- Quotation Order Date -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.order-date')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->order_date, 'd M Y') }}
            </p>
        </div>

        <!-- Quotation Expected Arrival Date -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.expected-arrival')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->expected_arrival_date, 'd M Y') }}
            </p>
        </div>

        <!-- Quotation Created At -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.created-at')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->created_at }}
            </p>
        </div>

        <!-- Quotation Expiration Date -->
        <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                @lang('b2b_suite::app.admin.quotes.view.expiration-date')
            </span>

            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->expiration_date, 'd M Y') }}
            </p>
        </div>
    </div>
</div>
