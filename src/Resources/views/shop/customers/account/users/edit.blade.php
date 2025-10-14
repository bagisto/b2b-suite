<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.users.edit.title')
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
                @lang('b2b_suite::app.shop.customers.account.users.edit.title')
            </h2>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.users.edit.before', ['user' => $user]) !!}

        <!-- Profile Edit Form -->
        <x-shop::form
            :action="route('shop.customers.account.users.update', $user->id)"
            method="PUT"
            enctype="multipart/form-data"
        >
            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.before', ['user' => $user]) !!}
    
            <!-- Image -->
            <x-shop::form.control-group class="mt-4">
                <x-shop::form.control-group.control
                    type="image"
                    class="max-md:[&>*]:[&>*]:rounded-full mb-0 rounded-xl !p-0 text-gray-700 max-md:grid max-md:justify-center"
                    name="image[]"
                    :label="trans('Image')"
                    :is-multiple="false"
                    accepted-types="image/*"
                    :src="$user->image_url"
                />

                <x-shop::form.control-group.error control-name="image[]" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.image.after', ['user' => $user]) !!}

            <!-- First Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.first-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="first_name"
                    rules="required"
                    value="{{ old('first_name') ?? $user->first_name }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.first-name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.edit.first-name')"
                />

                <x-shop::form.control-group.error control-name="first_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.first_name.after', ['user' => $user]) !!}

            <!-- Last Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.last-name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="last_name"
                    rules="required"
                    value="{{ old('last_name') ?? $user->last_name }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.last-name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.edit.last-name')"
                />

                <x-shop::form.control-group.error control-name="last_name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.last_name.after', ['user' => $user]) !!}

            <!-- Email -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.email')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="email"
                    rules="required|email"
                    value="{{ old('email') ?? $user->email }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.email')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.edit.email')"
                />

                <x-shop::form.control-group.error control-name="email" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.email.after', ['user' => $user]) !!}

            <!-- Phone -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.phone')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    name="phone"
                    rules="required|phone"
                    value="{{ old('phone') ?? $user->phone }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.phone')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.edit.phone')"
                />

                <x-shop::form.control-group.error control-name="phone" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.phone.after', ['user' => $user]) !!}

            <!-- Gender -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.gender')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="select"
                    class="mb-3"
                    name="gender"
                    rules="required"
                    value="{{ old('gender') ?? $user->gender }}"
                    :aria-label="trans('b2b_suite::app.shop.customers.account.users.edit.select-gender')"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.gender')"
                >
                    <option value="Other">
                        @lang('b2b_suite::app.shop.customers.account.users.edit.other')
                    </option>

                    <option value="Male">
                        @lang('b2b_suite::app.shop.customers.account.users.edit.male')
                    </option>

                    <option value="Female">
                        @lang('b2b_suite::app.shop.customers.account.users.edit.female')
                    </option>
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="gender" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.gender.after', ['user' => $user]) !!}

            <!-- DOB -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('b2b_suite::app.shop.customers.account.users.edit.dob')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="date"
                    name="date_of_birth"
                    value="{{ old('date_of_birth') ?? $user->date_of_birth }}"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.dob')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.users.edit.dob')"
                />

                <x-shop::form.control-group.error control-name="date_of_birth" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.date_of_birth.after', ['user' => $user]) !!}

            <!-- Role -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.users.edit.role')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="select"
                    class="mb-3"
                    name="company_role_id"
                    rules="required"
                    value="{{ old('company_role_id') ?: $user->company_role_id }}"
                    :aria-label="trans('b2b_suite::app.shop.customers.account.users.edit.select-role')"
                    :label="trans('b2b_suite::app.shop.customers.account.users.edit.role')"
                >
                    <option value="" disabled>@lang('b2b_suite::app.shop.customers.account.users.edit.select-role')</option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="company_role_id" />
            </x-shop::form.control-group>

            <!-- Active Status -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('b2b_suite::app.shop.customers.account.users.create.status')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="switch"
                    name="status"
                    value="1"
                    :checked="old('status', $user->status) ? true : false"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.active')"
                />

                <x-shop::form.control-group.error control-name="status" />
            </x-shop::form.control-group>

            <!-- Suspended -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label>
                    @lang('b2b_suite::app.shop.customers.account.users.create.is-suspended')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="switch"
                    name="is_suspended"
                    value="1"
                    :checked="old('is_suspended', $user->is_suspended) ? true : false"
                    :label="trans('b2b_suite::app.shop.customers.account.users.create.suspended')"
                />

                <x-shop::form.control-group.error control-name="is_suspended" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.role.after') !!}

            <button
                type="submit"
                class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
            >
                @lang('b2b_suite::app.shop.customers.account.users.edit.btn-save')
            </button>

            {!! view_render_event('bagisto.shop.customers.account.users.edit_form_controls.after', ['user' => $user]) !!}

        </x-shop::form>

        {!! view_render_event('bagisto.shop.customers.account.users.edit.after', ['user' => $user]) !!}

    </div>
</x-shop::layouts.account>
