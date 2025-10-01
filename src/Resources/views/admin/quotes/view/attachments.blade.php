<x-admin::accordion>
    <x-slot:header>
        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
            @lang('b2b_suite::app.admin.quotes.view.quote-attachments')
        </p>
    </x-slot>

    <x-slot:content>
        <div class="mt-4 flex flex-wrap gap-4">
            @foreach ($quote->attachments as $attachment)
                <div class="items-center justify-between border-b border-zinc-200 py-2">
                    
                    <div class="grid items-center gap-4">
                        @if(Str::startsWith($attachment->mime_type, 'image/'))
                            <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank" rel="noopener noreferrer">
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
    </x-slot:content>
</x-admin::accordion>