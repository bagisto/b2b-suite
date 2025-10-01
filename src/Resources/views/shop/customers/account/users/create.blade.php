<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.users.create.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="profile.edit" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-md:mb-5">
            <!-- Back Button -->
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.users.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium ltr:ml-2.5 rtl:mr-2.5 max-md:text-xl max-sm:text-base md:ltr:ml-0 md:rtl:mr-0">
                @lang('b2b_suite::app.shop.customers.account.users.create.title')
            </h2>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.users.create.before') !!}

        <!-- Profile Edit Form -->
        <x-shop::form
            :action="route('shop.customers.account.users.create')"
            enctype="multipart/form-data"
        >
            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.before') !!}
    
            <!-- Image -->
            <x-shop::form.control-group class="mt-4">
                <x-shop::form.control-group.control
                    type="image"
                    class="max-md:[&>*]:[&>*]:rounded-full mb-0 rounded-xl !p-0 text-gray-700 max-md:grid max-md:justify-center"
                    name="image[]"
                    :label="trans('Image')"
                    :is-multiple="false"
                    accepted-types="image/*"
                    :src="null"
                />

                <x-shop::form.control-group.error control-name="image[]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.image.after') !!}

            <!-- First Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.first-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="first_name"
                    rules="required"
                    value="{{ old('first_name') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.first-name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.create.first-name')"
                />

                <x-shop::form.control-group.error control-name="first_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.first_name.after') !!}

            <!-- Last Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.last-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="last_name"
                    rules="required"
                    value="{{ old('last_name') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.last-name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.create.last-name')"
                />

                <x-shop::form.control-group.error control-name="last_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.last_name.after') !!}

            <!-- Email -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.email')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="email"
                    rules="required|email"
                    value="{{ old('email') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.email')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.create.email')"
                />

                <x-shop::form.control-group.error control-name="email" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.email.after') !!}

            <!-- Phone -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.phone')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="phone"
                    rules="required|phone"
                    value="{{ old('phone') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.phone')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.create.phone')"
                />

                <x-shop::form.control-group.error control-name="phone" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.phone.after') !!}

            <!-- Gender -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.gender')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="select"
                    class="mb-3"
                    name="gender"
                    rules="required"
                    value="{{ old('gender') }}"
                    :aria-label="trans('b2b_suite::app.shop.customers.account.users.create.select-gender')"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.gender')"
                >
                    <option value="Other">
                        @lang('b2b_suite::app.shop.customers.account.users.create.other')
                    </option>

                    <option value="Male">
                        @lang('b2b_suite::app.shop.customers.account.users.create.male')
                    </option>

                    <option value="Female">
                        @lang('b2b_suite::app.shop.customers.account.users.create.female')
                    </option>
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="gender" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.gender.after') !!}

            <!-- DOB -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('b2b_suite::app.shop.customers.account.users.create.dob')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="date"
                    name="date_of_birth"
                    value="{{ old('date_of_birth') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.dob')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.create.dob')"
                />

                <x-shop::form.control-group.error control-name="date_of_birth" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.date_of_birth.after') !!}

            <!-- Role -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.create.role')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="select"
                    class="mb-3"
                    name="company_role_id"
                    rules="required"
                    value="{{ old('company_role_id') }}"
                    :aria-label="trans('b2b_suite::app.shop.customers.account.users.create.select-role')"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.role')"
                >
                    <option value="" disabled>@lang('b2b_suite::app.shop.customers.account.users.create.select-role')</option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="company_role_id" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.role.after') !!}

            <button
                type="submit"
                class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
            >
                @lang('b2b_suite::app.shop.customers.account.users.create.btn-save')
            </button>

            {!! view_render_event('bagisto.shop.customers.account.users.create_form_controls.after') !!}

        </x-shop::form>

        {!! view_render_event('bagisto.shop.customers.account.users.create.after') !!}

    </div>
</x-shop::layouts.account>
