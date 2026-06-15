<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.index.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.companies.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            @if (bouncer()->hasPermission('b2b.companies.create'))
                <a href="{{ route('admin.b2b.companies.create') }}">
                    <div class="primary-button">
                        @lang('b2b::app.admin.companies.index.create-btn')
                    </div>
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.b2b.companies.list.before') !!}

    <x-admin::datagrid :src="route('admin.b2b.companies.index')" />

    {!! view_render_event('bagisto.admin.b2b.companies.list.after') !!}

</x-admin::layouts>
