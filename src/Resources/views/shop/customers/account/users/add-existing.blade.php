<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.users.existing.title')
    </x-slot>

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
                @lang('b2b::app.shop.customers.account.users.existing.title')
            </h2>
        </div>

        <p class="mb-6 max-w-2xl text-sm text-zinc-500">
            @lang('b2b::app.shop.customers.account.users.existing.info')
        </p>

        <x-shop::form :action="route('shop.customers.account.users.invite')">
            <!-- Email of the existing platform customer. -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b::app.shop.customers.account.users.existing.email')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="email"
                    name="user_email"
                    rules="required|email"
                    value="{{ old('user_email') }}"
                    :label="trans('b2b::app.shop.customers.account.users.existing.email')"
                    :placeholder="trans('b2b::app.shop.customers.account.users.existing.email-placeholder')"
                />

                <x-shop::form.control-group.error control-name="user_email" />
            </x-shop::form.control-group>

            <!-- Role -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b::app.shop.customers.account.users.create.role')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="select"
                    class="mb-3"
                    name="company_role_id"
                    rules="required"
                    value="{{ old('company_role_id') }}"
                    :aria-label="trans('b2b::app.shop.customers.account.users.create.select-role')"
                    :label="trans('b2b::app.shop.customers.account.users.create.role')"
                >
                    <option value="" disabled>@lang('b2b::app.shop.customers.account.users.create.select-role')</option>

                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}">
                            {{ $role->name }}
                        </option>
                    @endforeach
                </x-shop::form.control-group.control>

                <x-shop::form.control-group.error control-name="company_role_id" />
            </x-shop::form.control-group>

            <button
                type="submit"
                class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
            >
                @lang('b2b::app.shop.customers.account.users.existing.btn-invite')
            </button>
        </x-shop::form>

        <!-- Pending Invitations -->
        <div class="mt-12">
            <p class="mb-4 text-xl font-medium text-gray-800 max-md:text-lg">
                @lang('b2b::app.shop.customers.account.users.existing.pending-title')
            </p>

            <x-shop::datagrid :src="route('shop.customers.account.users.add_existing')" />
        </div>
    </div>
</x-shop::layouts.account>
