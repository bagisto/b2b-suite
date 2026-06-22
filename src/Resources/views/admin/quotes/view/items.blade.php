@php
    $hasNegotiated = in_array($quote->status, ['open', 'negotiation', 'accepted', 'ordered', 'completed']);

    /**
     * The stored negotiated price folds the whole-quote discount into each unit. For display
     * we mirror the Send Quotation modal: show the per-unit price after the ITEM discount only
     * and surface the whole-quote discount as its own line (Sub Total → Discount on Total →
     * Negotiated Total).
     */
    $itemNegotiatedPrice = function ($item) {
        $price = (float) $item->base_price;
        $value = (float) $item->discount_value;

        if ($item->discount_type === 'percent') {
            return $price - ($price * $value / 100);
        }

        if ($item->discount_type === 'fixed') {
            return max(0, $price - $value);
        }

        return $price;
    };

    $negotiatedSubTotal = 0;

    foreach ($quote->items as $qi) {
        $negotiatedSubTotal += $itemNegotiatedPrice($qi) * (int) $qi->negotiated_qty;
    }

    $discountOnTotal = $negotiatedSubTotal - (float) $quote->base_negotiated_total;
@endphp

<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex justify-between p-1.5">
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @if ($quote->state == 'quotation')
                @lang('b2b::app.admin.quotes.view.quote-items')
            @else
                @lang('b2b::app.admin.purchase-orders.view.po-items')
            @endif
        </p>
    </div>

    @if (! $quote->items->count())
        <div class="p-4 text-sm font-medium text-zinc-500">
            @lang('b2b::app.admin.quotes.view.no-items')
        </div>
    @else
        <!-- Desktop: table -->
        <div class="overflow-x-auto p-4 max-md:hidden">
            <table class="w-full border ltr:text-left rtl:text-right text-sm dark:border-gray-800">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.product')</th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.name')</th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.price')</th>

                        @if ($hasNegotiated)
                            <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.discount')</th>
                            <th class="border-b px-4 py-2 ltr:text-right rtl:text-left text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.negotiated-price')</th>
                        @endif

                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.quantity')</th>
                        <th class="border-b px-4 py-2 ltr:text-right rtl:text-left text-gray-600 dark:border-gray-800 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.sub-total')</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($quote->items as $item)
                        @php
                            $additional = is_array($item->additional) ? $item->additional : json_decode($item->additional, true);
                        @endphp

                        <tr class="border-b dark:border-gray-800">
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                @if ($item->product)
                                    <a
                                        href="{{ route('admin.catalog.products.edit', $item->product->id) }}"
                                        class="inline-block h-[60px] w-[60px]"
                                    >
                                        <img
                                            src="{{ product_image()->getProductBaseImage($item->product)['small_image_url'] }}"
                                            alt="{{ $item->product->name }}"
                                            class="h-full w-full rounded border border-gray-300 object-cover hover:shadow"
                                            title="{{ $item->product->name }}"
                                        />
                                    </a>
                                @else
                                    <div class="flex h-[60px] w-[60px] items-center justify-center rounded border border-gray-300 bg-zinc-100 text-center text-xs font-medium text-zinc-500">
                                        @lang('b2b::app.admin.quotes.view.product-not-found')
                                    </div>
                                @endif
                            </td>

                            <td class="grid px-4 py-2 text-gray-600 dark:text-gray-300">
                                {{ $item->name }}

                                <span class="text-sm italic text-zinc-500">{{ $item->sku }}</span>

                                @if (isset($additional['attributes']))
                                    <div>
                                        @foreach ($additional['attributes'] as $attribute)
                                            @if (
                                                ! isset($attribute['attribute_type'])
                                                || $attribute['attribute_type'] !== 'file'
                                            )
                                                <b>{{ $attribute['attribute_name'] }} : </b>{{ $attribute['option_label'] }}<br>
                                            @else
                                                {{ $attribute['attribute_name'] }} :

                                                <a
                                                    href="{{ Storage::url($attribute['option_label']) }}"
                                                    class="text-blue-600 hover:underline"
                                                    download="{{ File::basename($attribute['option_label']) }}"
                                                >
                                                    {{ File::basename($attribute['option_label']) }}
                                                </a>

                                                <br>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ core()->formatBasePrice($item->base_price) }}</td>

                            @if ($hasNegotiated)
                                <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                    @if ($item->discount_type && (float) $item->discount_value > 0)
                                        <span class="font-medium text-green-600">{{ $item->discount_type === 'percent' ? (float) $item->discount_value . '%' : core()->formatBasePrice($item->discount_value) }}</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 ltr:text-right rtl:text-left font-semibold text-gray-600 dark:text-gray-300">{{ core()->formatBasePrice($itemNegotiatedPrice($item)) }}</td>
                            @endif

                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $hasNegotiated ? $item->negotiated_qty : $item->qty }}</td>

                            <td class="px-4 py-2 ltr:text-right rtl:text-left font-semibold text-gray-600 dark:text-gray-300">
                                {{ $hasNegotiated
                                    ? core()->formatBasePrice($itemNegotiatedPrice($item) * $item->negotiated_qty)
                                    : core()->formatBasePrice($item->base_price * $item->qty) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    @if ($hasNegotiated)
                        <tr class="border-t dark:border-gray-800">
                            <td colspan="6" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-600 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.sub-total')</td>
                            <td colspan="1" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-zinc-500 dark:text-gray-300">{{ core()->formatBasePrice($negotiatedSubTotal) }}</td>
                        </tr>

                        @if ($discountOnTotal > 0)
                            <tr class="border-t dark:border-gray-800">
                                <td colspan="6" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-600 dark:text-gray-300">
                                    @lang('b2b::app.admin.quotes.view.discount-on-total')
                                    @if ($quote->discount_type && (float) $quote->discount_value > 0)
                                        <span class="font-normal text-zinc-500">({{ $quote->discount_type === 'percent' ? (float) $quote->discount_value . '%' : core()->formatBasePrice($quote->discount_value) }})</span>
                                    @endif
                                </td>
                                <td colspan="1" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-green-600">− {{ core()->formatBasePrice($discountOnTotal) }}</td>
                            </tr>
                        @endif

                        <tr class="border-t dark:border-gray-800">
                            <td colspan="6" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-600 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.negotiated-total')</td>
                            <td colspan="1" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-800 dark:text-white">{{ core()->formatBasePrice($quote->base_negotiated_total) }}</td>
                        </tr>
                    @else
                        <tr class="border-t dark:border-gray-800">
                            <td colspan="4" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-600 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.grand-total')</td>
                            <td colspan="1" class="px-4 py-2 ltr:text-right rtl:text-left font-bold text-gray-800 dark:text-white">{{ core()->formatBasePrice($quote->base_total) }}</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>

        <!-- Mobile: stacked cards -->
        <div class="flex flex-col gap-3 p-4 md:hidden">
            @foreach ($quote->items as $item)
                @php
                    $additional = is_array($item->additional) ? $item->additional : json_decode($item->additional, true);
                @endphp

                <div class="rounded border border-gray-200 p-3 dark:border-gray-800">
                    <div class="flex gap-3">
                        @if ($item->product)
                            <a
                                href="{{ route('admin.catalog.products.edit', $item->product->id) }}"
                                class="inline-block h-[60px] w-[60px] shrink-0"
                            >
                                <img
                                    src="{{ product_image()->getProductBaseImage($item->product)['small_image_url'] }}"
                                    alt="{{ $item->product->name }}"
                                    class="h-full w-full rounded border border-gray-300 object-cover"
                                    title="{{ $item->product->name }}"
                                />
                            </a>
                        @else
                            <div class="flex h-[60px] w-[60px] shrink-0 items-center justify-center rounded border border-gray-300 bg-zinc-100 text-center text-xs font-medium text-zinc-500">
                                @lang('b2b::app.admin.quotes.view.product-not-found')
                            </div>
                        @endif

                        <div class="grid">
                            <span class="text-sm font-medium text-gray-800 dark:text-white">{{ $item->name }}</span>
                            <span class="text-xs italic text-zinc-500">{{ $item->sku }}</span>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-y-1.5 border-t border-gray-100 pt-3 text-sm dark:border-gray-800">
                        <span class="text-gray-500 dark:text-gray-400">@lang('b2b::app.admin.quotes.view.price')</span>
                        <span class="ltr:text-right rtl:text-left font-medium text-gray-800 dark:text-white">{{ core()->formatBasePrice($item->base_price) }}</span>

                        @if ($hasNegotiated && $item->discount_type && (float) $item->discount_value > 0)
                            <span class="text-gray-500 dark:text-gray-400">@lang('b2b::app.admin.quotes.view.discount')</span>
                            <span class="ltr:text-right rtl:text-left font-medium text-green-600">{{ $item->discount_type === 'percent' ? (float) $item->discount_value . '%' : core()->formatBasePrice($item->discount_value) }}</span>
                        @endif

                        @if ($hasNegotiated)
                            <span class="text-gray-500 dark:text-gray-400">@lang('b2b::app.admin.quotes.view.negotiated-price')</span>
                            <span class="ltr:text-right rtl:text-left font-semibold text-gray-800 dark:text-white">{{ core()->formatBasePrice($itemNegotiatedPrice($item)) }}</span>
                        @endif

                        <span class="text-gray-500 dark:text-gray-400">@lang('b2b::app.admin.quotes.view.quantity')</span>
                        <span class="ltr:text-right rtl:text-left font-medium text-gray-800 dark:text-white">{{ $hasNegotiated ? $item->negotiated_qty : $item->qty }}</span>

                        <span class="text-gray-500 dark:text-gray-400">@lang('b2b::app.admin.quotes.view.sub-total')</span>
                        <span class="ltr:text-right rtl:text-left font-semibold text-gray-800 dark:text-white">
                            {{ $hasNegotiated
                                ? core()->formatBasePrice($itemNegotiatedPrice($item) * $item->negotiated_qty)
                                : core()->formatBasePrice($item->base_price * $item->qty) }}
                        </span>
                    </div>
                </div>
            @endforeach

            <!-- Totals -->
            <div class="rounded border border-gray-200 p-3 dark:border-gray-800">
                @if ($hasNegotiated)
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-700 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.sub-total')</span>
                        <span class="font-bold text-zinc-500">{{ core()->formatBasePrice($negotiatedSubTotal) }}</span>
                    </div>

                    @if ($discountOnTotal > 0)
                        <div class="mt-2 flex items-center justify-between">
                            <span class="font-bold text-gray-700 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.discount-on-total')</span>
                            <span class="font-bold text-green-600">− {{ core()->formatBasePrice($discountOnTotal) }}</span>
                        </div>
                    @endif

                    <div class="mt-2 flex items-center justify-between">
                        <span class="font-bold text-gray-700 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.negotiated-total')</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ core()->formatBasePrice($quote->base_negotiated_total) }}</span>
                    </div>
                @else
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-gray-700 dark:text-gray-300">@lang('b2b::app.admin.quotes.view.grand-total')</span>
                        <span class="font-bold text-gray-800 dark:text-white">{{ core()->formatBasePrice($quote->base_total) }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
