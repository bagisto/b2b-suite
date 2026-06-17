<!-- Attachments -->
<div class="mt-4 flex flex-col rounded-xl border bg-white p-5 max-xl:flex-auto max-sm:p-2">
    <p class="mb-4 text-base font-semibold text-gray-800">
        @if ($quote->state == 'quotation')
            @lang('b2b::app.shop.customers.account.quotes.view.quote-attachments')
        @else
            @lang('b2b::app.shop.customers.account.purchase-orders.view.po-attachments')
        @endif
    </p>

    <div class="mt-4 flex flex-wrap gap-4">
        @forelse ($quote->attachments as $attachment)
            @php
                $downloadUrl = route('shop.customers.account.quotes.download', ['id' => $quote->id, 'attachment' => $attachment->id]);
            @endphp

            @if (Str::startsWith($attachment->mime_type, 'image/'))
                <a href="{{ $downloadUrl }}" title="@lang('b2b::app.shop.customers.account.quotes.view.download')">
                    <img src="{{ asset('storage/' . $attachment->path) }}" alt="{{ $attachment->name }}" class="h-[100px] w-[100px] cursor-pointer rounded border border-gray-300 object-cover hover:shadow" />
                </a>
            @else
                <a
                    href="{{ $downloadUrl }}"
                    class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 transition-all hover:shadow"
                    title="@lang('b2b::app.shop.customers.account.quotes.view.download')"
                >
                    <span class="icon-download shrink-0 text-3xl text-navyBlue"></span>

                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800" style="max-width: 16rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $attachment->name }}</p>
                        <p class="text-xs uppercase text-zinc-500">{{ pathinfo($attachment->name, PATHINFO_EXTENSION) }}</p>
                    </div>
                </a>
            @endif
        @empty
            <div class="text-sm font-medium text-zinc-500">
                @lang('b2b::app.shop.customers.account.quotes.view.no-attachments')
            </div>
        @endforelse
    </div>
</div>
