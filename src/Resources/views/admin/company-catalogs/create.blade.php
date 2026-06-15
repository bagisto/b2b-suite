<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.company-catalogs.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.create.before') !!}

    @include('b2b::admin.company-catalogs.form', [
        'mode'   => 'create',
        'action' => route('admin.b2b.company_catalogs.store'),
        'method' => 'POST',
    ])

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.create.after') !!}
</x-admin::layouts>
