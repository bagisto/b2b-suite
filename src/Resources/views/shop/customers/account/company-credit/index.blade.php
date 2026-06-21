@php
    $available = (float) $credit->credit_limit - (float) $credit->outstanding_balance;
@endphp

<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.company-credit.title')
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <!-- Header -->
        <div class="mb-8 flex items-center max-sm:mb-5">
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.profile.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('b2b::app.shop.customers.account.company-credit.title')
            </h2>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.company-credit.before', ['credit' => $credit]) !!}

        <!-- Summary cards (ordering mirrors the admin panel). -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- Row 1: Outstanding balance + Available credit -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.company-credit.outstanding-balance')</span>
                
                <p class="mt-1.5 text-xl font-semibold {{ (float) $credit->outstanding_balance > 0 ? 'text-red-600' : 'text-zinc-800' }}">{{ core()->formatBasePrice(max(0, (float) $credit->outstanding_balance)) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.company-credit.available-credit')</span>
                
                <p class="mt-1.5 text-xl font-semibold {{ $available > 0 ? 'text-green-700' : 'text-zinc-800' }}">{{ core()->formatBasePrice($available) }}</p>
            </div>

            <!-- Row 2: Credit limit + Status -->
            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.company-credit.credit-limit')</span>
                
                <p class="mt-1.5 text-xl font-semibold text-zinc-800">{{ core()->formatBasePrice($credit->credit_limit) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5">
                <span class="text-xs uppercase tracking-wide text-zinc-400">@lang('b2b::app.shop.customers.account.company-credit.status')</span>
                
                <p class="mt-1.5">
                    <span class="{{ $credit->status ? 'label-active' : 'label-canceled' }}">
                        @lang('b2b::app.shop.customers.account.company-credit.'.($credit->status ? 'enabled' : 'disabled'))
                    </span>
                </p>
            </div>
        </div>

        <!-- Credit History -->
        <p class="mb-3 mt-8 text-lg font-medium text-zinc-800">@lang('b2b::app.shop.customers.account.company-credit.history')</p>

        <x-shop::datagrid :src="route('shop.customers.account.credit.index')" />

        {!! view_render_event('bagisto.shop.customers.account.company-credit.after', ['credit' => $credit]) !!}
    </div>
</x-shop::layouts.account>
