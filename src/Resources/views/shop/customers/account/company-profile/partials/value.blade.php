@php
    $value = $customer[$attribute->code] ?? null;
@endphp

@switch($attribute->type)
    @case('select')
        @php $option = ($value !== null && $value !== '') ? $attribute->options()->find($value) : null; @endphp
        {{ $option?->name ?: '—' }}
        @break

    @case('multiselect')
    @case('checkbox')
        @php
            $ids = ($value !== null && $value !== '') ? array_filter(explode(',', $value)) : [];
            $names = $ids ? $attribute->options()->whereIn('id', $ids)->pluck('name')->implode(', ') : '';
        @endphp
        {{ $names ?: '—' }}
        @break

    @case('boolean')
        {{ $value ? trans('b2b::app.shop.customers.account.company-profile.index.yes') : trans('b2b::app.shop.customers.account.company-profile.index.no') }}
        @break

    @case('image')
        @if ($value)
            <a
                href="{{ Storage::url($value) }}"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    src="{{ Storage::url($value) }}"
                    class="h-[45px] w-[45px] overflow-hidden rounded border hover:border-gray-400"
                    alt="{{ $attribute->name }}"
                />
            </a>
        @else
            —
        @endif
        @break

    @case('file')
        @if ($value)
            <a
                href="{{ Storage::url($value) }}"
                class="inline-flex items-center gap-1.5 text-blue-600 hover:underline"
            >
                <i class="icon-download text-lg"></i>
                {{ \Illuminate\Support\Str::limit(\Illuminate\Support\Facades\File::basename($value), 30) }}
            </a>
        @else
            —
        @endif
        @break

    @default
        {{ ($value !== null && $value !== '') ? $value : '—' }}
@endswitch
