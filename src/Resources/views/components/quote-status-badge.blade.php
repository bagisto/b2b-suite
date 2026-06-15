@props(['status' => ''])

@php
    /**
     * Status → [text color, background color]. Inline styles are used so the badge
     * renders correctly without relying on a global stylesheet / Tailwind purge.
     */
    $colors = [
        'draft'       => ['#3f3f46', '#f4f4f5'],
        'open'        => ['#0044F2', '#e6edff'],
        'negotiation' => ['#9a6700', '#fff3da'],
        'accepted'    => ['#1f7a33', '#e7f6ea'],
        'ordered'     => ['#060c3b', '#e8eaf2'],
        'completed'   => ['#1f7a33', '#e7f6ea'],
        'expired'     => ['#52525b', '#f4f4f5'],
        'rejected'    => ['#b42318', '#fde8e6'],
    ];

    [$textColor, $bgColor] = $colors[$status] ?? ['#3f3f46', '#f4f4f5'];

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
