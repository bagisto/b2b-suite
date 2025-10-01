<!-- Attachments -->
<div class="mt-4 flex flex-col rounded-xl border bg-white p-5 max-xl:flex-auto max-sm:p-2">
    <p class="mb-4 text-base font-semibold text-gray-800">
        @if ($quote->state == 'quotation')
            @lang('b2b_suite::app.shop.customers.account.quotes.view.quote-attachments')
        @else
            @lang('b2b_suite::app.shop.customers.account.purchase-orders.view.po-attachments')
        @endif
    </p>

    <div class="mt-4 flex flex-wrap gap-4">
        @foreach ($quote->attachments as $attachment)
            <div class="items-center justify-between border-b border-zinc-200 py-2">
                
                <div class="grid items-center gap-4">
                    @if(Str::startsWith($attachment->mime_type, 'image/'))
                        <a href="{{ route('shop.customers.account.quotes.download', ['id' => $quote->id, 'attachment' => $attachment->id]) }}">
                            <img src="{{ asset('storage/' . $attachment->path) }}" alt="{{ $attachment->name }}" class="h-[100px] w-[100px] cursor-pointer rounded border border-gray-300 object-cover hover:shadow" title="@lang('b2b_suite::app.shop.customers.account.quotes.view.download')" />
                        </a>
                    @endif
                </div>
            </div>
        @endforeach

        @if (! $quote->attachments->count())
            <div class="text-sm font-medium text-zinc-500">
                @lang('b2b_suite::app.shop.customers.account.quotes.view.no-attachments')
            </div>
        @endif
    </div>
</div>
