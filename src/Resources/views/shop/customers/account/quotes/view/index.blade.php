<div class="mt-8 grid gap-8 max-sm:p-2">
    {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.quote_information.before", ['quote' => $quote]) !!}

    <!-- Quote Information -->
    <div class="w-full gap-8 rounded-xl border bg-white p-5 max-xl:flex-auto">
        <p class="mb-4 text-base font-semibold text-gray-800">
            @if ($quote->state == 'quotation')
                @lang('b2b::app.shop.customers.account.quotes.view.quote-information')
            @else
                @lang('b2b::app.shop.customers.account.purchase-orders.view.po-information')
            @endif
        </p>

        <!-- Quote Name -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.name')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->name }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.name.after", ['quote' => $quote]) !!}

        <!-- Quote Description -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.description')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->description }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.description.after", ['quote' => $quote]) !!}

        <!-- Quote Status -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.status')
            </p>

            <div>
                <x-b2b::quote-status-badge :status="$quote->status" />
            </div>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.status.after", ['quote' => $quote]) !!}

        <!-- Quote Order Date -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.order-date')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->order_date ?: '-' }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.order_date.after", ['quote' => $quote]) !!}

        <!-- Quote Expected Arrival Date -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.expected-arrival')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->expected_arrival_date ?: '-' }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.expiration_date.after", ['quote' => $quote]) !!}

        <!-- Quote Created Date -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.created-at')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->created_at }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.created_at.after", ['quote' => $quote]) !!}

        <!-- Quote Expiration Date -->
        <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-200 px-8 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.expiration-date')
            </p>

            <p class="text-sm font-medium text-zinc-500">
                {{ $quote->expiration_date }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.expiration_date.after", ['quote' => $quote]) !!}
    </div>

    {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.quote_information.after", ['quote' => $quote]) !!}

    <!-- Company Information -->
    <div class="w-full gap-8 rounded-xl border bg-white p-5 max-xl:flex-auto">
        <p class="mb-4 text-base font-semibold text-gray-800">
            @lang('b2b::app.shop.customers.account.quotes.view.company-information')
        </p>

        <!-- Company Name -->
        <div class="grid w-full grid-cols-[2fr_3fr] gap-4 border-b border-zinc-200 px-3 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.company-name')
            </p>

            <p class="text-right text-sm font-medium text-zinc-500">
                {{ $quote->company->name }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.company_name.after", ['quote' => $quote]) !!}

        <!-- Company Email -->
        <div class="grid w-full grid-cols-[2fr_3fr] gap-4 border-b border-zinc-200 px-3 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.company-email')
            </p>

            <p class="text-right text-sm font-medium text-zinc-500">
                {{ $quote->company->email }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.company_email.after", ['quote' => $quote]) !!}

        <!-- Company Phone -->
        <div class="grid w-full grid-cols-[2fr_3fr] gap-4 border-b border-zinc-200 px-3 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.company-phone')
            </p>

            <p class="text-right text-sm font-medium text-zinc-500">
                {{ $quote->company->phone }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.company_phone.after", ['quote' => $quote]) !!}

        <!-- Sales Representative Name -->
        <div class="grid w-full grid-cols-[2fr_3fr] gap-4 border-b border-zinc-200 px-3 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.sr-name')
            </p>

            <p class="text-right text-sm font-medium text-zinc-500">
                {{ $quote->agent->name }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.agent_name.after", ['quote' => $quote]) !!}

        <!-- Sales Representative Email -->
        <div class="grid w-full grid-cols-[2fr_3fr] gap-4 border-b border-zinc-200 px-3 py-3 max-md:px-0">
            <p class="text-sm font-medium">
                @lang('b2b::app.shop.customers.account.quotes.view.sr-email')
            </p>

            <p class="text-right text-sm font-medium text-zinc-500">
                {{ $quote->agent->email }}
            </p>
        </div>

        {!! view_render_event("bagisto.shop.customers.account.quote.view_form_controls.controls.agent_email.after", ['quote' => $quote]) !!}

    </div>
</div>
