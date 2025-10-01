<x-admin::layouts>
    <x-slot:title>
        @lang('b2b_suite::app.admin.company-attributes.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b_suite::app.admin.company-attributes.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            {!! view_render_event('bagisto.admin.customers.attributes.index.create_button.before') !!}

            @if (bouncer()->hasPermission('b2b_suite.company_attributes.create'))
                <a
                    href="{{ route('admin.customers.attributes.edit_mapping') }}"
                    class="secondary-button"
                >
                    @lang('b2b_suite::app.admin.company-attributes.index.mapping-btn')
                </a>

                <a
                    href="{{ route('admin.customers.attributes.create') }}"
                    class="primary-button"
                >
                    @lang('b2b_suite::app.admin.company-attributes.index.create-btn')
                </a>
            @endif

            {!! view_render_event('bagisto.admin.customers.attributes.index.create_button.after') !!}
        </div>
    </div>

    {!! view_render_event('bagisto.admin.customers.attributes.list.before') !!}

    <x-admin::datagrid :src="route('admin.customers.attributes.index')" />

    {!! view_render_event('bagisto.admin.customers.attributes.list.after') !!}

</x-admin::layouts>