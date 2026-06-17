<div class="rounded-xl border border-zinc-200 bg-white p-6 max-md:p-5">
    <p class="mb-5 border-b border-zinc-100 pb-3 text-base font-semibold text-gray-900">
        {{ $group->name }}
    </p>

    <div class="flex flex-col gap-5">
        @foreach ($group->custom_attributes as $attribute)
            <div class="flex flex-col gap-1">
                <span class="text-xs font-medium uppercase tracking-wide text-zinc-400">
                    {{ $attribute->name }}
                </span>

                <span class="break-words text-sm font-medium text-gray-800">
                    @include('b2b::shop.companies.account.profile.partials.value', [
                        'attribute' => $attribute,
                        'customer'  => $customer,
                    ])
                </span>
            </div>
        @endforeach
    </div>
</div>
