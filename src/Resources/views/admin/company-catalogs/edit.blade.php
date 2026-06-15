<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.company-catalogs.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.edit.before', ['catalog' => $catalog]) !!}

    @include('b2b::admin.company-catalogs.form', [
        'mode'      => 'edit',
        'catalog'   => $catalog,
        'products'  => $products,
        'companies' => $companies,
        'action'    => route('admin.b2b.company_catalogs.update', $catalog->id),
        'method'    => 'PUT',
    ])

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.edit.after', ['catalog' => $catalog]) !!}
</x-admin::layouts>
