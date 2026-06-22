<!-- Quote Attachments -->
<div class="box-shadow rounded bg-white dark:bg-gray-900">
    <div class="flex justify-between p-1.5">
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.quotes.view.quote-attachments')
        </p>
    </div>

    <div class="flex flex-wrap gap-4 px-4 pb-4">
        @forelse ($quote->attachments as $attachment)
            @if (Str::startsWith($attachment->mime_type, 'image/'))
                <a
                    href="{{ asset('storage/' . $attachment->path) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="@lang('b2b::app.shop.customers.account.quotes.view.download')"
                >
                    <img
                        src="{{ asset('storage/' . $attachment->path) }}"
                        alt="{{ $attachment->name }}"
                        class="h-[100px] w-[100px] cursor-pointer rounded border border-gray-300 object-cover hover:shadow"
                    />
                </a>
            @else
                <a
                    href="{{ asset('storage/' . $attachment->path) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition-all hover:shadow dark:border-gray-700"
                    title="@lang('b2b::app.shop.customers.account.quotes.view.download')"
                >
                    <span class="icon-view shrink-0 text-2xl text-gray-500 dark:text-gray-300"></span>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white" style="max-width: 16rem;">{{ $attachment->name }}</p>
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-300">{{ pathinfo($attachment->name, PATHINFO_EXTENSION) }}</p>
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