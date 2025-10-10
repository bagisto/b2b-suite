<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.roles.edit.title')
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

    {!! view_render_event('bagisto.shop.customers.account.roles.edit.before', ['role' => $role]) !!}

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
                @lang('b2b_suite::app.shop.customers.account.roles.edit.title')
            </h2>
        </div>

        {!! view_render_event('bagisto.admin.settings.roles.edit.edit_form_controls.before', ['role' => $role]) !!}

        <x-shop::form
             :action="route('shop.customers.account.roles.update', $role->id)"
             method="PUT"
        >
            <!-- Name -->
            <x-shop::form.control-group>
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.roles.edit.name')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="text"
                    id="name"
                    name="name"
                    rules="required"
                    value="{{ old('name') ?: $role->name }}"
                    :label="trans('b2b_suite::app.shop.customers.account.roles.edit.name')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.roles.edit.name')"
                />

                <x-shop::form.control-group.error control-name="name" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.roles.edit_form_controls.name.after', ['role' => $role]) !!}

            <!-- Description -->
            <x-shop::form.control-group class="!mb-0">
                <x-shop::form.control-group.label class="required">
                    @lang('b2b_suite::app.shop.customers.account.roles.edit.description')
                </x-shop::form.control-group.label>

                <x-shop::form.control-group.control
                    type="textarea"
                    id="description"
                    name="description"
                    rules="required"
                    value="{{ old('description') ?: $role->description }}"
                    :label="trans('b2b_suite::app.shop.customers.account.roles.edit.description')"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.roles.edit.description')"
                />

                <x-shop::form.control-group.error control-name="description" />
            </x-shop::form.control-group>

            {!! view_render_event('bagisto.shop.customers.account.roles.edit_form_controls.description.after', ['role' => $role]) !!}

            <!-- Access Control Input Fields -->
            <div class="box-shadow rounded bg-white py-4">
                <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('b2b_suite::app.shop.customers.account.roles.edit.access-control')
                </p>

                <!-- Create Role for -->
                <v-access-control>
                    <!-- Shimmer Effect -->
                    <div class="mb-4">
                        <div class="shimmer mb-1.5 h-4 w-24"></div>

                        <div class="custom-select h-11 w-full rounded-md border bg-white px-3 py-2.5 text-sm font-normal text-gray-600 transition-all hover:border-gray-400"></div>
                    </div>

                    <!-- Roles Checkbox -->
                    <x-b2b_suite::shimmer.tree />
                </v-access-control>
            </div>

            <button
                type="submit"
                class="primary-button m-0 block rounded-2xl px-11 py-3 text-center text-base max-md:w-full max-md:max-w-full max-md:rounded-lg max-md:py-1.5"
            >
                @lang('b2b_suite::app.shop.customers.account.roles.create.btn-save')
            </button>

        </x-shop::form>

        {!! view_render_event('bagisto.shop.customers.account.roles.edit.edit_form_controls.after', ['role' => $role]) !!}

    </div>
    
    {!! view_render_event('bagisto.shop.customers.account.users.create.after', ['role' => $role]) !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-access-control-template"
        >
            <div>
                <!-- Permission Type -->
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label class="required">
                        @lang('b2b_suite::app.shop.customers.account.roles.edit.permissions')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="select"
                        name="permission_type"
                        id="permission_type"
                        rules="required"
                        :label="trans('b2b_suite::app.shop.customers.account.roles.edit.permissions')"
                        :placeholder="trans('b2b_suite::app.shop.customers.account.roles.edit.permissions')"
                        v-model="permission_type"
                    >
                        <option value="custom">
                            @lang('b2b_suite::app.shop.customers.account.roles.edit.custom-permissions')
                        </option>

                        <option value="all">
                            @lang('b2b_suite::app.shop.customers.account.roles.edit.all-permissions')
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
                        :value="json_encode($role->permissions ?? [])"
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
                        permission_type: "{{ $role->permission_type }}"
                    };
                }
            })
        </script>
    @endPushOnce
</x-shop::layouts.account>
