<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex justify-between p-1.5">
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @if ($quote->state == 'quotation')
                @lang('b2b::app.admin.quotes.view.quote-information')
            @else
                @lang('b2b::app.admin.purchase-orders.view.po-information')
            @endif
        </p>
    </div>

    <div class="grid grid-cols-1 gap-x-4 px-4 pb-4 sm:grid-cols-2 xl:grid-cols-3">
        <!-- Name -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.name')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->name }}
            </p>
        </div>

        <!-- Description -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.description')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->description }}
            </p>
        </div>

        <!-- Status -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.status')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                @lang("b2b::app.admin.quotes.view.$quote->status")
            </p>
        </div>

        <!-- Order Date -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.order-date')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->order_date, 'd M Y') }}
            </p>
        </div>

        <!-- Expected Arrival Date -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.expected-arrival')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->expected_arrival_date, 'd M Y') }}
            </p>
        </div>

        <!-- Created At -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.created-at')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ $quote->created_at }}
            </p>
        </div>

        <!-- Expiration Date -->
        <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
            <span class="text-xs text-gray-500 dark:text-gray-400">
                @lang('b2b::app.admin.quotes.view.expiration-date')
            </span>

            <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                {{ core()->formatDate($quote->expiration_date, 'd M Y') }}
            </p>
        </div>
    </div>
</div>
