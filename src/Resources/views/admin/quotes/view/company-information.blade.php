<!-- Company Information -->
<x-admin::accordion>
    <x-slot:header>
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @lang('b2b_suite::app.admin.quotes.view.company-information')
        </p>
    </x-slot>

    <x-slot:content>
        <div class="grid grid-cols-12 gap-4">
            <!-- Company Name -->
            <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                    @lang('b2b_suite::app.admin.quotes.view.company-name')
                </span>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company->name }}
                </p>
            </div>

            <!-- Company Email -->
            <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('b2b_suite::app.admin.quotes.view.company-email')
                </span>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company->email }}
                </p>
            </div>

            <!-- Company Phone -->
            <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('b2b_suite::app.admin.quotes.view.company-phone')
                </span>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->company->phone }}
                </p>
            </div>

            <!-- Sales Representative Name -->
            <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('b2b_suite::app.admin.quotes.view.sr-name')
                </span>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->agent->name }}
                </p>
            </div>

            <!-- Sales Representative Email -->
            <div class="grid grid-cols-2 border-b border-zinc-200 px-8 py-3 dark:border-gray-800 max-md:px-0">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    @lang('b2b_suite::app.admin.quotes.view.sr-email')
                </span>

                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white">
                    {{ $quote->agent->email }}
                </p>
            </div>
        </div>
    </x-slot:content>
</x-admin::accordion>
