{!! view_render_event("bagisto.shop.customers.account.profile.edit_form_controls.{$group->code}.before", ['customer' => $customer]) !!}

<div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900 max-md:p-4">
    <p class="mb-4 text-base font-semibold text-gray-900 dark:text-white">
        {{ $group->name }}
    </p>

    @foreach ($group->custom_attributes as $attribute)
        {!! view_render_event("bagisto.shop.customers.account.profile.edit_form_controls.{$group->code}.controls.before", ['customer' => $customer]) !!}

        <x-shop::form.control-group :class="$loop->last ? '!mb-0' : ''">
            <x-shop::form.control-group.label>
                {!! $attribute->name . ($attribute->is_required ? '<span class="required"></span>' : '') !!}
            </x-shop::form.control-group.label>

            @include('b2b::shop.companies.account.profile.partials.controls', [
                'attribute' => $attribute,
                'customer'  => $customer,
            ])

            <x-shop::form.control-group.error :control-name="$attribute->code" />
        </x-shop::form.control-group>

        {!! view_render_event("bagisto.shop.customers.account.profile.edit.form.{$group->code}.controls.after", ['customer' => $customer]) !!}
    @endforeach
</div>

{!! view_render_event("bagisto.shop.customers.account.profile.edit.form.{$group->code}.after", ['customer' => $customer]) !!}
