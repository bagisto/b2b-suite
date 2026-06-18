<!-- Company Information -->
<x-admin::accordion>
    <x-slot:header>
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.quotes.view.company-information')
        </p>
    </x-slot>

    <x-slot:content>
        <div class="grid grid-cols-1 gap-x-4 sm:grid-cols-2">
            <!-- Business Name -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.business-name')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company?->businessName() ?: '—' }}
                </p>
            </div>

            <!-- Contact Name -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.company-name')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company?->name }}
                </p>
            </div>

            <!-- Company Email -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.company-email')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company->email }}
                </p>
            </div>

            <!-- Company Phone -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.company-phone')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company->phone }}
                </p>
            </div>

            <!-- Sales Representative Name -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.sr-name')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company?->salesRep?->name ?? '—' }}
                </p>
            </div>

            <!-- Sales Representative Email -->
            <div class="border-b border-zinc-200 py-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    @lang('b2b::app.admin.quotes.view.sr-email')
                </span>

                <p class="mt-1 break-words text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company?->salesRep?->email ?? '—' }}
                </p>
            </div>
        </div>
    </x-slot:content>
</x-admin::accordion>
