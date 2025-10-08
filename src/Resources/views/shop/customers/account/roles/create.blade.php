<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.roles.create.title')
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

    {!! view_render_event('bagisto.shop.customers.account.roles.create.before') !!}

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-md:mb-5">
            <!-- Back Button -->
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.users.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('b2b_suite::app.shop.customers.account.roles.create.title')
            </h2>
        </div>

        {!! view_render_event('bagisto.admin.settings.roles.create.create_form_controls.before') !!}

        <x-shop::form :action="route('shop.customers.account.roles.store')">

            <!-- Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.roles.create.name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    id="name"
                    name="name"
                    rules="required"
                    value="{{ old('name') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.roles.create.name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.roles.create.name')"
                />

                <x-shop::form.control-group.error control-name="name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.roles.create_form_controls.name.after') !!}

            <!-- Description -->
            <x-shop::form.control-group class="!mb-0">
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.roles.create.description')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="textarea"
                    id="description"
                    name="description"
                    rules="required"
                    value="{{ old('description') }}"
                    :label="trans('b2b_suite::app.shop.customers.account.roles.create.description')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.roles.create.description')"
                />

                <x-shop::form.control-group.error control-name="description" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.roles.create_form_controls.description.after') !!}

            <!-- Access Control Input Fields -->
            <div class="box-shadow rounded bg-white py-4">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('b2b_suite::app.shop.customers.account.roles.create.access-control')
                </p>

                <!-- Create Role for -->
                <v-access-control>
                    <!-- Shimmer Effect -->
                    <div class="mb-4">
                        <div class="shimmer mb-1.5 h-4 w-24"></div>

                        <div class="custom-select h-11 w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400"></div>
                    </div>

                    <!-- Roles Checkbox -->
                    <x-admin::shimmer.tree />
                </v-access-control>
            </div>

            <button
                type="submit"
                class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
            >
                @lang('b2b_suite::app.shop.customers.account.roles.create.btn-save')
            </button>

        </x-shop::form>

        {!! view_render_event('bagisto.shop.customers.account.roles.create.create_form_controls.after') !!}

    </div>
    
    {!! view_render_event('bagisto.shop.customers.account.users.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-access-control-template"
        >
            <div>
                <!-- Permission Type -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required">
                        @lang('b2b_suite::app.shop.customers.account.roles.create.permissions')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        name="permission_type"
                        id="permission_type"
                        rules="required"
                        :label="trans('b2b_suite::app.shop.customers.account.roles.create.permissions')"
                        :placeholder="trans('b2b_suite::app.shop.customers.account.roles.create.permissions')"
                        v-model="permission_type"
                    >
                        <option value="custom">
                            @lang('b2b_suite::app.shop.customers.account.roles.create.custom-permissions')
                        </option>

                        <option value="all">
                            @lang('b2b_suite::app.shop.customers.account.roles.create.all-permissions')
                        </option>
                    </x-shop::form.control-group.control>

                    <x-shop::form.control-group.error control-name="permission_type" />
                </x-shop::form.control-group>

                <div v-if="permission_type == 'custom'">
                    <x-shop::form.control-group.error control-name="permissions" />

                    <x-b2b_suite::tree.view
                        input-type="checkbox"
                        value-field="key"
                        id-field="key"
                        :items="json_encode(b2b_suite_acl()->getItems())"
                        :fallback-locale="config('app.fallback_locale')"
                    />
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-access-control', {
                template: '#v-access-control-template',

                data() {
                    return {
                        permission_type: 'custom'
                    };
                },
            })
        </script>
    @endPushOnce
</x-shop::layouts.account>