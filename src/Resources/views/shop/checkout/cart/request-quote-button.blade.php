@php
    if (! auth()->guard('customer')->check()) {
        return;
    }
    
    $cart = cart()->getCart();
    $minimumAmount = (float) core()->getConfigData('b2b_suite.quotes.settings.minimum_amount');
    $minimumAmountMessage = core()->getConfigData('b2b_suite.quotes.settings.minimum_amount_message');
@endphp

@if ($cart && $cart->items->count() > 0)
    <button
        v-if="cart.grand_total >= {{ $minimumAmount }}"
        type="button"
        class="secondary-button mt-4 place-self-end rounded-2xl px-11 py-3 max-md:my-4 max-md:max-w-full max-md:rounded-lg max-md:py-3 max-md:text-sm max-sm:w-full max-sm:py-2"
        @click="$emitter.emit('open-request-quote-modal')"
    >
        @lang('b2b_suite::app.shop.checkout.cart.request-quote-button')
    </button>
    <div 
        class="rounded-lg border border-red-200 bg-red-50 p-3" 
        v-else
    >
        <p class="text-sm text-red-600">
            {{ $minimumAmountMessage ?? __('b2b_suite::app.shop.checkout.cart.minimum-amount-required') }}
        </p>
    </div>

    <template
        v-if="cart.grand_total >= {{ $minimumAmount }}"
    >
        @include('b2b_suite::shop.checkout.cart.request-quote-modal')
    </template>
@endif
