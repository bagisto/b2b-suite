@php
    $available = (float) $credit->credit_limit - (float) $credit->outstanding_balance;
@endphp

<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.companies.account.credit.title')
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="flex-auto max-md:px-4">
        <div class="mb-4">
            <p class="text-xl font-medium text-zinc-500">
                @lang('b2b::app.shop.companies.account.credit.title')
            </p>
            <p class="text-sm text-zinc-500">@lang('b2b::app.shop.companies.account.credit.info')</p>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 p-4">
                <span class="text-sm text-zinc-500">@lang('b2b::app.shop.companies.account.credit.credit-limit')</span>
                <p class="mt-1 text-xl font-semibold text-zinc-800">{{ core()->formatBasePrice($credit->credit_limit) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4">
                <span class="text-sm text-zinc-500">@lang('b2b::app.shop.companies.account.credit.outstanding-balance')</span>
                <p class="mt-1 text-xl font-semibold {{ (float) $credit->outstanding_balance > 0 ? 'text-red-600' : 'text-zinc-800' }}">{{ core()->formatBasePrice($credit->outstanding_balance) }}</p>
            </div>

            <div class="rounded-xl border border-zinc-200 p-4">
                <span class="text-sm text-zinc-500">@lang('b2b::app.shop.companies.account.credit.available-credit')</span>
                <p class="mt-1 text-xl font-semibold {{ $available > 0 ? 'text-green-700' : 'text-zinc-800' }}">{{ core()->formatBasePrice($available) }}</p>
            </div>
        </div>

        <!-- History -->
        <p class="mb-3 mt-6 text-base font-medium text-zinc-800">@lang('b2b::app.shop.companies.account.credit.history')</p>

        @if (! $transactions->count())
            <p class="rounded-xl border border-zinc-200 p-6 text-center text-sm text-zinc-500">
                @lang('b2b::app.shop.companies.account.credit.no-transactions')
            </p>
        @else
            <div class="overflow-x-auto rounded-xl border border-zinc-200">
                <table class="w-full text-left text-sm" style="min-width: 34rem;">
                    <thead class="bg-zinc-50">
                        <tr class="text-xs font-medium uppercase text-zinc-500">
                            <th class="px-4 py-3">@lang('b2b::app.shop.companies.account.credit.date')</th>
                            <th class="px-4 py-3">@lang('b2b::app.shop.companies.account.credit.operation')</th>
                            <th class="px-4 py-3 text-right">@lang('b2b::app.shop.companies.account.credit.amount')</th>
                            <th class="px-4 py-3 text-right">@lang('b2b::app.shop.companies.account.credit.balance')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $txn)
                            <tr class="border-t border-zinc-100">
                                <td class="whitespace-nowrap px-4 py-2.5 text-zinc-600">{{ core()->formatDate($txn->created_at, 'd M Y') }}</td>
                                <td class="px-4 py-2.5 text-zinc-800">
                                    @lang('b2b::app.shop.companies.account.credit.operations.'.$txn->operation)
                                    @if ($txn->order_id)
                                        <span class="block text-xs text-zinc-400">@lang('b2b::app.shop.companies.account.credit.order') #{{ $txn->order?->increment_id ?? $txn->order_id }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-right font-medium {{ $txn->operation === 'purchased' ? 'text-red-600' : 'text-zinc-700' }}">{{ core()->formatBasePrice($txn->amount) }}</td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-right text-zinc-700">{{ core()->formatBasePrice($txn->outstanding_balance_after) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</x-shop::layouts.account>
