@php
    if (! auth()->guard('customer')->check()) {
        return;
    }
    
    $cart = cart()->getCart();
    $minimumAmount = (float) core()->getConfigData('b2b_suite.quotes.settings.minimum_amount');
    $cartTotal = $cart?->grand_total ?? 0;
    $canRequestQuote = $cartTotal >= $minimumAmount;
    $minimumAmountMessage = core()->getConfigData('b2b_suite.quotes.settings.minimum_amount_message');
@endphp

@if ($cart && $cart->items->count() > 0)
    @if ($canRequestQuote)
        <button
            type="button"
            class="secondary-button mt-4 place-self-end rounded-2xl px-11 py-3 max-md:my-4 max-md:max-w-full max-md:rounded-lg max-md:py-3 max-md:text-sm max-sm:w-full max-sm:py-2"
            @click="$emitter.emit('open-request-quote-modal')"
        >
            @lang('b2b_suite::app.shop.checkout.cart.request-quote-button')
        </button>
    @else
        <div class="rounded-lg border border-red-200 bg-red-50 p-3">
            <p class="text-sm text-red-600">
                {{ $minimumAmountMessage ?? __('b2b_suite::app.shop.checkout.cart.minimum-amount-required') }}
            </p>
        </div>
    @endif

    @if ($canRequestQuote)
        @include('b2b_suite::shop.checkout.cart.request-quote-modal')
    @endif
@endif
