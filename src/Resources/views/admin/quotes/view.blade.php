<x-admin::layouts>
    <x-slot:title>
        @lang('b2b_suite::app.admin.quotes.view.title', ['id' => $quote->quotation_number])
    </x-slot>

    <!-- Header -->
    <div class="grid">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            {!! view_render_event('bagisto.admin.customers.quote.title.before', ['quote' => $quote]) !!}

            <div class="flex items-center gap-2.5">
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                    @lang('b2b_suite::app.admin.quotes.view.title', ['id' => $quote->quotation_number])
                </p>

                <!-- Order Status -->
                <span class="label-pending mx-1.5 text-sm">
                    @lang("b2b_suite::app.admin.quotes.view.$quote->status")
                </span>
            </div>

            {!! view_render_event('bagisto.admin.customers.quote.title.after', ['quote' => $quote]) !!}

            <!-- Back Button -->
            <a
                href="{{ route('admin.customers.quotes.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('b2b_suite::app.admin.quotes.view.back-btn')
            </a>
        </div>
    </div>

    <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
        <!-- Quote details -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                {!! view_render_event('bagisto.admin.customers.quote.left_component.before', ['quote' => $quote]) !!}

                <!-- Quote Information -->
                @include('b2b_suite::admin.quotes.view.quote-information')

                <!-- Quote Items -->
                @include('b2b_suite::admin.quotes.view.items')
            </div>

            <!-- Right Component -->
            <div class="flex w-[525px] flex-col gap-2 max-sm:w-full">
                {!! view_render_event('bagisto.admin.sales.order.right_component.before', ['quote' => $quote]) !!}

                <!-- Company Information -->
                @include('b2b_suite::admin.quotes.view.company-information')

                <!-- Quote Attachments -->
                @include('b2b_suite::admin.quotes.view.attachments')
            </div>
        </div>
                
        <!-- Quote Messages -->
        @include('b2b_suite::admin.quotes.view.messages', ['quote' => $quote])
    </div>
</x-admin::layouts>