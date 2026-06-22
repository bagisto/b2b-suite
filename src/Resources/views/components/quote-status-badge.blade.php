@props(['status' => ''])

@php
    /**
     * Colours come from the single source of truth on the model (shared with the shop
     * datagrids) so the listing badge and this view-page badge always match. Inline styles
     * keep the badge purge-proof.
     */
    [$textColor, $bgColor] = \Webkul\B2BSuite\Models\CustomerQuote::statusColor($status);

    $labelKey = 'b2b::app.shop.customers.account.quotes.view.'.$status;
    $label = trans($labelKey);

    if ($label === $labelKey) {
        $label = \Illuminate\Support\Str::title($status);
    }
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold']) }}
    style="color: {{ $textColor }}; background-color: {{ $bgColor }};"
>
    {{ $label }}
</span>
