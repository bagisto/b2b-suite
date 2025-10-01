<div class="overflow-x-auto">
    <table class="w-full border text-left text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.name')</th>
                <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.negotiated-price')</th>
                <th class="px-4 py-2">@lang('b2b_suite::app.shop.customers.account.quotes.view.quantity')</th>
            </tr>
        </thead>
        
        <tbody>
            @foreach ($quote->items as $item)
                <tr class="border-b">
                    <td class="px-4 py-2">
                        <div class="grid">
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
                            {{ $item->name }}
                        </div>
                    </td>

                    <td class="px-4 py-2">
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.control
                                type="text"
                                class="max-w-[110px]"
                                name="items[{{ $item->id }}][negotiated_price]"
                                :value="old('items.'.$item->id.'.negotiated_price') ?? $item->negotiated_price"
                                rules="required|decimal:4"
                                :label="trans('b2b_suite::app.shop.customers.account.quotes.view.negotiated-price')"
                                :placeholder="trans('b2b_suite::app.shop.customers.account.quotes.view.negotiated-price')"
                            />

                            <x-shop::form.control-group.error control-name="items[{{ $item->id }}][negotiated_price]" />
                        </x-shop::form.control-group>
                    </td>
                    
                    <td class="px-4 py-2">
                        <!-- Quantity -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.control
                                type="number"
                                class="max-w-[110px]"
                                name="items[{{ $item->id }}][negotiated_qty]"
                                :value="old('items.'.$item->id.'.negotiated_qty') ?? $item->negotiated_qty"
                                rules="required|numeric|min:1"
                                :label="trans('b2b_suite::app.shop.customers.account.quotes.view.quantity')"
                                :placeholder="trans('b2b_suite::app.shop.customers.account.quotes.view.quantity')"
                            />

                            <x-shop::form.control-group.error control-name="items[{{ $item->id }}][negotiated_qty]" />
                        </x-shop::form.control-group>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>