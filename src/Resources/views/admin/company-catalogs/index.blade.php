<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.company-catalogs.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.company-catalogs.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('b2b.company-catalogs.create'))
                <a href="{{ route('admin.b2b.company_catalogs.create') }}">
                    <div class="primary-button">
                        @lang('b2b::app.admin.company-catalogs.index.create-btn')
                    </div>
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.list.before') !!}

    <x-admin::datagrid :src="route('admin.b2b.company_catalogs.index')" />

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.list.after') !!}
</x-admin::layouts>
