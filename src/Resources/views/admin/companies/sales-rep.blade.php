{{--
    Sales representative (account manager) selector — a fixed control (not a company custom
    attribute) bound to customers.sales_rep_id, embedded in the right-column "Sales Rep &
    Approval" card. $company is absent on create.
--}}
@php
    $selectedRep = old('sales_rep_id', $company->sales_rep_id ?? '');
@endphp

<x-admin::form.control-group>
    <x-admin::form.control-group.label>
        @lang('b2b::app.admin.companies.sales-rep.label')
    </x-admin::form.control-group.label>

    <x-admin::form.control-group.control
        type="select"
        name="sales_rep_id"
        :value="$selectedRep"
    >
        <option value="">@lang('b2b::app.admin.companies.sales-rep.none')</option>

        @foreach ($admins as $admin)
            <option value="{{ $admin->id }}">{{ $admin->name }}</option>
        @endforeach
    </x-admin::form.control-group.control>

    <x-admin::form.control-group.error control-name="sales_rep_id" />
</x-admin::form.control-group>
