<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.company-profile.index.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="company.profile" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    @php
        $canEdit ??= true;

        $column1 = $attributeGroups->where('column', 1)->filter(fn ($g) => $g->custom_attributes->isNotEmpty());
        $column2 = $attributeGroups->where('column', 2)->filter(fn ($g) => $g->custom_attributes->isNotEmpty());
    @endphp

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">

        {!! view_render_event('bagisto.shop.customers.account.company-profile.before', ['customer' => $customer]) !!}

        <!-- Header -->
        <div class="mb-8 flex items-center justify-between gap-4 max-sm:mb-5 max-sm:flex-wrap">
            <div class="flex items-center">
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.profile.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    @lang('b2b::app.shop.customers.account.company-profile.index.title')
                </h2>
            </div>

            @if ($canEdit)
                <a
                    href="{{ route('shop.customers.account.company_profile.edit') }}"
                    class="primary-button rounded-lg px-8 py-2.5 text-center text-base max-md:px-6 max-md:py-2 max-md:text-sm"
                >
                    @lang('b2b::app.shop.customers.account.company-profile.index.edit-btn')
                </a>
            @endif
        </div>

        @if ($column1->isEmpty() && $column2->isEmpty())
            <div class="mt-6 flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-200 bg-white py-16 text-center">
                <p class="text-base font-medium text-gray-600">
                    @lang('b2b::app.shop.customers.account.company-profile.index.no-attributes')
                </p>
            </div>
        @else
            <div class="flex items-start gap-6 max-md:flex-wrap max-md:gap-4">
                <!-- Left Column -->
                <div class="flex flex-1 flex-col gap-6 max-md:w-full max-md:flex-auto max-md:gap-4">
                    @foreach ($column1 as $group)
                        @include('b2b::shop.customers.account.company-profile.partials.summary-card', ['group' => $group, 'customer' => $customer])
                    @endforeach
                </div>

                <!-- Right Column -->
                @if ($column2->isNotEmpty())
                    <div class="flex flex-1 flex-col gap-6 max-md:w-full max-md:flex-auto max-md:gap-4">
                        @foreach ($column2 as $group)
                            @include('b2b::shop.customers.account.company-profile.partials.summary-card', ['group' => $group, 'customer' => $customer])
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        {!! view_render_event('bagisto.shop.customers.account.company-profile.after', ['customer' => $customer]) !!}
    </div>
</x-shop::layouts.account>
