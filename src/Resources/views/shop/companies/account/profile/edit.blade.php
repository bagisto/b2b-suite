<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.customers.account.profile.edit.edit-profile')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="profile.edit" />
        @endSection
    @endif

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        
    
        {!! view_render_event('bagisto.shop.customers.account.profile.edit.before', ['customer' => $customer]) !!}

        <!-- Profile Edit Form -->
        <x-shop::form
            action="{{ route('shop.companies.account.profile.update', $customer->id) }}"
            enctype="multipart/form-data"
            method="PUT"
        >
            <!-- Page Header -->
            <div class="grid gap-2.5">
                <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                    <h2 class="text-2xl font-medium ltr:ml-2.5 rtl:mr-2.5 max-md:text-xl max-sm:text-base md:ltr:ml-0 md:rtl:mr-0">
                        @lang('shop::app.customers.account.profile.edit.edit-profile')
                    </h2>

                    <div class="flex items-center gap-x-2.5">
                        <!-- Back Button -->
                        <a
                            href="{{ route('shop.customers.account.profile.index') }}"
                            class="transparent-button px-5 py-2.5"
                        >
                            @lang('b2b_suite::app.shop.companies.account.profile.edit.btn-back')
                        </a>

                        <!-- Save Button -->
                        <button
                            type="submit"
                            class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
                        >
                            @lang('shop::app.customers.account.profile.edit.save')
                        </button>
                    </div>
                </div>
            </div>

            {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.before', ['customer' => $customer]) !!}
    
            <!-- Image -->
            <x-shop::form.control-group class="mt-4">
                <x-shop::form.control-group.control
                    type="image"
                    class="max-md:[&>*]:[&>*]:rounded-full mb-0 rounded-xl !p-0 text-gray-700 max-md:grid max-md:justify-center"
                    name="image[]"
                    :label="trans('Image')"
                    :is-multiple="false"
                    accepted-types="image/*"
                    :src="$customer->image_url"
                />

                <x-shop::form.control-group.error control-name="image[]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.image.after') !!}

            <div class="mt-8 flex gap-8 max-xl:flex-wrap">
                @php
                    $groupedColumns = $attributeGroups->groupBy('column');

                    $isSingleColumn = $groupedColumns->count() !== 2;
                @endphp

                @foreach ($groupedColumns as $column => $groups)
                    {!! view_render_event("bagisto.shop.customers.account.profile.edit_form_controls.form.column_{$column}.before", ['customer' => $customer]) !!}

                    <div class="flex flex-col gap-8 {{ $column == 1 ? 'flex-1 max-xl:flex-auto' : 'w-[360px] max-w-full max-sm:w-full' }}">
                        @foreach ($groups as $group)
                            @php $customAttributes = $group->custom_attributes()->get(); @endphp

                            @if ($customAttributes->isNotEmpty())
                                {!! view_render_event("bagisto.shop.customers.account.profile.edit_form_controls.{$group->code}.before", ['customer' => $customer]) !!}

                                <div class="rounded-xl border bg-white p-5">
                                    <p class="mb-4 text-base font-semibold text-gray-800">
                                        {{ $group->name }}
                                    </p>

                                    @foreach ($customAttributes as $attribute)
                                        {!! view_render_event("bagisto.shop.customers.account.profile.edit_form_controls.{$group->code}.controls.before", ['customer' => $customer]) !!}
                                        
                                        <x-shop::form.control-group>
                                            <x-shop::form.control-group.label class="!mt-5">
                                                {!! $attribute->name . ($attribute->is_required ? '<span class="required"></span>' : '') !!}

                                            </x-shop::form.control-group.label>

                                            @include ('b2b_suite::shop.companies.account.profile.edit.controls', [
                                                'attribute' => $attribute,
                                                'customer'  => $customer,
                                            ])
                                            
                                            <x-shop::form.control-group.error :control-name="$attribute->code" />
                                        </x-shop::form.control-group>

                                        {!! view_render_event("bagisto.shop.customers.account.profile.edit.form.{$group->code}.controls.after", ['customer' => $customer]) !!}
                                    @endforeach
                                </div>

                                {!! view_render_event("bagisto.shop.customers.account.profile.edit.form.{$group->code}.after", ['customer' => $customer]) !!}
                            @endif
                        @endforeach
                        
                        @if ($column == 2)
                            <div class="rounded-xl border bg-white p-5">
                                <p class="mb-4 text-base font-semibold text-gray-800">
                                    @lang('b2b_suite::app.shop.companies.account.profile.edit.settings')
                                </p>

                                <!-- Current Password -->
                                <x-shop::form.control-group class="mt-4">
                                    <x-shop::form.control-group.label>
                                        @lang('shop::app.customers.account.profile.edit.current-password')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        name="current_password"
                                        value=""
                                        :label="trans('shop::app.customers.account.profile.edit.current-password')"
                                        :placeholder="trans('shop::app.customers.account.profile.edit.current-password')"
                                    />

                                    <x-shop::form.control-group.error control-name="current_password" />
                                </x-shop::form.control-group>

                                {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.old_password.after') !!}

                                <!-- New Password -->
                                <x-shop::form.control-group class="mt-4">
                                    <x-shop::form.control-group.label>
                                        @lang('shop::app.customers.account.profile.edit.new-password')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        name="new_password"
                                        value=""
                                        :label="trans('shop::app.customers.account.profile.edit.new-password')"
                                        :placeholder="trans('shop::app.customers.account.profile.edit.new-password')"
                                    />

                                    <x-shop::form.control-group.error control-name="new_password" />
                                </x-shop::form.control-group>

                                {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.new_password.after') !!}

                                <!-- New Password Confirmation -->
                                <x-shop::form.control-group class="mt-4">
                                    <x-shop::form.control-group.label>
                                        @lang('shop::app.customers.account.profile.edit.confirm-password')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="password"
                                        name="new_password_confirmation"
                                        rules="confirmed:@new_password"
                                        value=""
                                        :label="trans('shop::app.customers.account.profile.edit.confirm-password')"
                                        :placeholder="trans('shop::app.customers.account.profile.edit.confirm-password')"
                                    />

                                    <x-shop::form.control-group.error control-name="new_password_confirmation" />
                                </x-shop::form.control-group>

                                {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.new_password_confirmation.after') !!}
                            </div>
                        @endif
                    </div>

                    {!! view_render_event("bagisto.shop.customers.account.profile.edit.form.column_{$column}.after", ['customer' => $customer]) !!}
                @endforeach
                
                <div class="flex w-[360px] max-w-full flex-col gap-8 max-sm:w-full">
                    
                </div>
            </div>

            <div class="mb-4 flex select-none items-center gap-1.5">
                <input
                    type="checkbox"
                    name="subscribed_to_news_letter"
                    id="is-subscribed"
                    class="peer hidden"
                    @checked($customer->subscribed_to_news_letter)
                />

                <label
                    class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                    for="is-subscribed"
                ></label>

                <label
                    class="cursor-pointer select-none text-base text-zinc-500 ltr:pl-0 rtl:pr-0 max-md:text-sm"
                    for="is-subscribed"
                >
                    @lang('shop::app.customers.account.profile.edit.subscribe-to-newsletter')
                </label>
            </div>

            {!! view_render_event('bagisto.shop.customers.account.profile.edit_form_controls.after', ['customer' => $customer]) !!}

        </x-shop::form>

        {!! view_render_event('bagisto.shop.customers.account.profile.edit.after', ['customer' => $customer]) !!}

    </div>
</x-shop::layouts.account>
