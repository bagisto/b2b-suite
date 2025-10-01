<div class="mt-4 flex flex-col rounded-xl border bg-white p-5 max-xl:flex-auto max-sm:p-2">
    <!-- Update Quote Items modal -->
    <x-shop::form 
        method="PUT"
        action="{{ route('shop.customers.account.quotes.add_to_cart', $quote->id) }}"
    >
        <div class="flex items-center justify-between gap-x-2.5">
            <p class="mb-4 text-base font-semibold text-gray-800">
                @if ($quote->state == 'quotation')
                    @lang('b2b_suite::app.shop.customers.account.quotes.view.quote-items')
                @else
                    @lang('b2b_suite::app.shop.customers.account.purchase-orders.view.po-items')
                @endif
            </p>
            
            @if (
                $quote->items->count()
                && $quote->state == 'quotation'
                && $quote->status == 'accepted'
            )
                <button
                    type="submit"
                    class="primary-button mb-4 cursor-pointer p-3 text-sm"
                >
                    @lang('b2b_suite::app.shop.customers.account.quotes.view.btn-add-to-cart')
                </button>
            @endif
        </div>

        @if (! $quote->items->count())
            <div class="text-sm font-medium text-zinc-500">
                @lang('b2b_suite::app.shop.customers.account.quotes.view.no-items')
            </div>
        @else
            <!-- For Desktop View -->
            <div class="overflow-x-auto max-md:hidden">
                <table class="w-full border text-left text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.product')</th>
                            <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.name')</th>
                            <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.price')</th>
                            <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.quantity')</th>
                            <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.sub-total')</th>

                            @if (in_array($quote->status, ['accepted', 'ordered', 'completed']))
                                <th class="px-4 py-2 text-right">@lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-price')</th>
                                <th class="px-4 py-2 text-right">@lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-qty')</th>
                                <th class="px-4 py-2 text-right">@lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-total')</th>
                            @endif
                        </tr>
                    </thead>
                    
                    <tbody>
                        @foreach ($quote->items as $item)
                            <tr class="border-b">
                                <td class="px-4 py-2">
                                    @if ($item->product)
                                        <a 
                                            href="{{ route('shop.product_or_category.index', $item->product->url_key) }}" 
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
                                        <div class="flex h-[60px] w-[60px] items-center justify-center rounded border border-gray-300 bg-zinc-100 text-xs font-medium text-zinc-500">
                                            @lang('b2b_suite::app.shop.customers.account.quotes.view.product-not-found')
                                        </div>
                                    @endif
                                </td>
                                
                                <td class="grid gap-2 px-4 py-2">
                                    {{ $item->name }}
                                    
                                    <span class="text-sm italic text-zinc-500">{{ $item->sku }}</span>
                                    @php
                                        $item->additional = json_decode($item->additional, true);
                                    @endphp

                                    @if (isset($item->additional['attributes']))
                                        <div>
                                            @foreach ($item->additional['attributes'] as $attribute)
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
                                <td class="px-4 py-2">{{ $item->price }}</td>
                                <td class="px-4 py-2">{{ $item->qty }}</td>
                                <td class="px-4 py-2">
                                    {{ core()->formatPrice(($item->price*$item->qty), $quote->currency_code) }}
                                </td>
                                
                                @if (in_array($quote->status, ['accepted', 'ordered', 'completed']))
                                    <!-- Negotiated Price -->
                                    <td class="px-4 py-2 text-right font-semibold">
                                        {{ $item->negotiated_price ? core()->formatPrice($item->negotiated_price, $quote->currency_code) : '-' }}
                                    </td>
                                    
                                    <!-- Negotiated Quantity -->
                                    <td class="px-4 py-2 text-right font-semibold">
                                        {{ $item->negotiated_qty }}
                                    </td>
                                    
                                    <!-- Negotiated Total -->
                                    <td class="px-4 py-2 text-right font-semibold">
                                        {{ core()->formatPrice(($item->negotiated_price * $item->negotiated_qty), $quote->currency_code) }}
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        <tr class="border-t">
                            <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '7' : '4' }}" class="px-4 py-2 text-right font-bold">
                                @lang('b2b_suite::app.shop.customers.account.quotes.view.grand-total')
                            </td>
                            <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '6' : '3' }}" class="px-4 py-2 text-right font-bold text-zinc-500">
                                {{ core()->formatPrice($quote->total, $quote->currency_code) }}
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '7' : '4' }}" class="px-4 py-2 text-right font-bold">
                                @lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-total')
                            </td>
                            <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '6' : '3' }}" class="px-4 py-2 text-right text-lg font-bold text-navyBlue">
                                {{ core()->formatPrice($quote->negotiated_total, $quote->currency_code) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- For Mobile View -->
            <div class="md:hidden">
                @foreach ($quote->items as $item)
                    <div class="w-full p-4 border rounded-md transition-all hover:bg-gray-50 [&>*]:border-0 mb-4 last:mb-0">
                        <div class="flex items-center gap-4">
                            @if ($item->product)
                                <a href="{{ $item->product->url_key }}" class="inline-block h-[60px] w-[60px] flex-shrink-0">
                                    <img 
                                        src="{{ product_image()->getProductBaseImage($item->product)['small_image_url'] }}" 
                                        alt="{{ $item->product->name }}" 
                                        class="h-full w-full rounded border border-gray-300 object-cover hover:shadow" 
                                        title="{{ $item->product->name }}" 
                                    />
                                </a>
                            @else
                                <div class="flex h-[60px] w-[60px] flex-shrink-0 items-center justify-center rounded border border-gray-300 bg-zinc-100 text-xs font-medium text-zinc-500">
                                    @lang('b2b_suite::app.shop.customers.account.quotes.view.product-not-found')
                                </div>
                            @endif

                            <div class="flex-grow">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ $item->name }}
                                </p>

                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                    @lang('b2b_suite::app.shop.customers.account.quotes.view.sku'): {{ $item->sku }}
                                </p>

                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                    @lang('b2b_suite::app.shop.customers.account.quotes.view.price'): 
                                    {{ $item->price ? core()->formatPrice($item->price, $quote->currency_code) : '-' }}
                                </p>

                                <p class="mt-1 text-xs font-medium text-zinc-500">
                                    @lang('b2b_suite::app.shop.customers.account.quotes.view.quantity'): {{ $item->qty }}
                                </p>

                                <p class="mt-1 text-sm font-semibold text-gray-800">
                                    @lang('b2b_suite::app.shop.customers.account.quotes.view.sub-total'): 
                                    {{ core()->formatPrice(($item->price*$item->qty), $quote->currency_code) }}
                                </p>
                                
                                @if (in_array($quote->status, ['accepted', 'ordered', 'completed']))
                                    <!-- Negotiated Price -->
                                    <p class="mt-1 text-xs font-medium text-zinc-500">
                                        @lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-price'):
                                        {{ $item->negotiated_price ? core()->formatPrice($item->negotiated_price, $quote->currency_code) : '-' }}
                                    </p>
                                    
                                    <!-- Negotiated Quantity -->
                                    <p class="mt-1 text-xs font-medium text-zinc-500">
                                        @lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-qty'):
                                        {{ $item->negotiated_qty }}
                                    </p>
                                    
                                    <!-- Negotiated Total -->
                                    <p class="mt-1 text-sm font-semibold text-gray-800">
                                        @lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-total'):
                                        {{ core()->formatPrice(($item->negotiated_price * $item->negotiated_qty), $quote->currency_code) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <div class="flex justify-end border-t pt-4">
                    <div class="mt-4 w-full max-w-xs">
                        <div class="flex justify-between text-sm font-bold text-gray-800">
                            <span>
                                @lang('b2b_suite::app.shop.customers.account.quotes.view.grand-total')
                            </span>

                            <span class="text-zinc-500">
                                {{ core()->formatPrice($quote->total, $quote->currency_code) }}
                            </span>
                        </div>

                        <div class="mt-2 flex justify-between text-sm font-bold text-gray-800">
                            <span>
                                @lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-total')
                            </span>

                            <span class="text-zinc-500">
                                {{ core()->formatPrice($quote->negotiated_total, $quote->currency_code) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-shop::form>
</div>