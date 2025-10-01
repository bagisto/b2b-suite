
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex justify-between p-1.5">
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @if ($quote->state == 'quotation')
                @lang('b2b_suite::app.admin.quotes.view.quote-items')
            @else
                @lang('b2b_suite::app.admin.purchase-orders.view.po-items')
            @endif
        </p>
    </div>

    @if (! $quote->items->count())
        <div class="text-sm font-medium text-zinc-500">
            @lang('b2b_suite::app.admin.quotes.view.no-items')
        </div>
    @else
        <div class="overflow-x-auto p-4 max-md:hidden">
            <table class="w-full border text-left text-sm dark:border-gray-800">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.product')
                        </th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.name')
                        </th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.price')
                        </th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.quantity')
                        </th>
                        <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.sub-total')
                        </th>

                        @if (in_array($quote->status, ['accepted', 'ordered', 'completed']))
                            <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                @lang('b2b_suite::app.admin.quotes.view.negotiated-price')
                            </th>
                            <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                @lang('b2b_suite::app.admin.quotes.view.negotiated-qty')
                            </th>
                            <th class="border-b px-4 py-2 text-gray-600 dark:border-gray-800 dark:text-gray-300">
                                @lang('b2b_suite::app.admin.quotes.view.negotiated-total')
                            </th>
                        @endif
                    </tr>
                </thead>
                
                <tbody>
                    @foreach ($quote->items as $item)
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
                                    <div class="flex h-[60px] w-[60px] items-center justify-center rounded border border-gray-300 bg-zinc-100 text-xs font-medium text-zinc-500">
                                        @lang('b2b_suite::app.admin.quotes.view.product-not-found')
                                    </div>
                                @endif
                            </td>
                            
                            <td class="grid px-4 py-2 text-gray-600 dark:text-gray-300">
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
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                {{ $item->base_price }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                {{ $item->qty }}
                            </td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">
                                {{ core()->formatBasePrice($item->base_price * $item->qty) }}
                            </td>
                            
                            @if (in_array($quote->status, ['accepted', 'ordered', 'completed']))
                                <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                    {{ $item->base_negotiated_price }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                    {{ $item->negotiated_qty }}
                                </td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-600 dark:text-gray-300">
                                    {{ core()->formatBasePrice($item->base_negotiated_price * $item->negotiated_qty) }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="border-t dark:border-gray-800">
                        <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '7' : '5' }}" class="px-4 py-2 text-right font-bold text-gray-600 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.grand-total')
                        </td>
                        <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '6' : '4' }}" class="px-4 py-2 text-right font-bold text-gray-600 text-zinc-500 dark:text-gray-300">
                            {{ core()->formatBasePrice($quote->base_total) }}
                        </td>
                    </tr>
                    <tr class="border-t dark:border-gray-800">
                        <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '7' : '5' }}" class="px-4 py-2 text-right font-bold text-gray-600 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.quotes.view.negotiated-total')
                        </td>
                        <td colspan="{{ in_array($quote->status, ['accepted', 'ordered', 'completed']) ? '6' : '4' }}" class="px-4 py-2 text-right font-bold text-gray-600 text-zinc-500 dark:text-gray-300">
                            {{ core()->formatBasePrice($quote->base_negotiated_total) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>