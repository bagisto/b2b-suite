<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.companies.account.profile.index.title')
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
        /* When the member's role lacks the edit permission the form is shown read-only. */
        $canEdit ??= true;
    @endphp

    <div class="flex-auto max-md:px-4">

        {!! view_render_event('bagisto.shop.companies.account.profile.before', ['customer' => $customer]) !!}

        <x-shop::form
            action="{{ route('shop.companies.account.profile.update', $customer->id) }}"
            enctype="multipart/form-data"
            method="PUT"
        >
            <!-- Page Header -->
            <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
                <div class="flex items-center gap-2.5">
                    <!-- Back Button (mobile) -->
                    <a
                        class="grid md:hidden"
                        href="{{ route('shop.customers.account.profile.index') }}"
                    >
                        <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                    </a>

                    <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base">
                        @lang('b2b::app.shop.companies.account.profile.index.title')
                    </h2>
                </div>

                <div class="flex items-center gap-x-2.5">
                    <!-- Back Button (desktop) -->
                    <a
                        href="{{ route('shop.customers.account.profile.index') }}"
                        class="transparent-button px-5 py-2.5 hover:bg-gray-100 max-md:hidden dark:hover:bg-gray-800"
                    >
                        @lang('b2b::app.shop.companies.account.profile.edit.btn-back')
                    </a>

                    <!-- Save Button (hidden for view-only members) -->
                    @if ($canEdit)
                        <button
                            type="submit"
                            class="primary-button rounded-lg px-8 py-2.5 text-center text-base max-md:px-6 max-md:py-2 max-md:text-sm"
                        >
                            @lang('b2b::app.shop.companies.account.profile.index.save-btn')
                        </button>
                    @endif
                </div>
            </div>

            <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-300">
                @lang('b2b::app.shop.companies.account.profile.index.info')
            </p>

            {!! view_render_event('bagisto.shop.companies.account.profile.controls.before', ['customer' => $customer]) !!}

            @php
                $column1 = $attributeGroups->where('column', 1);
                $column2 = $attributeGroups->where('column', 2);
            @endphp

            @if ($attributeGroups->isEmpty())
                <div class="mt-6 flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-200 bg-white py-16 text-center dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-base font-medium text-gray-600 dark:text-gray-300">
                        @lang('b2b::app.shop.companies.account.profile.index.no-attributes')
                    </p>
                </div>
            @else
                <fieldset @disabled(! $canEdit)>
                <div class="mt-6 flex gap-6 max-xl:flex-wrap max-md:mt-4 max-md:gap-4">
                    <!-- Left Column -->
                    <div class="flex flex-1 flex-col gap-6 max-xl:flex-auto max-md:gap-4">
                        @foreach ($column1 as $group)
                            @if ($group->custom_attributes->isNotEmpty())
                                @include('b2b::shop.companies.account.profile.partials.group', [
                                    'group'    => $group,
                                    'customer' => $customer,
                                ])
                            @endif
                        @endforeach
                    </div>

                    <!-- Right Column -->
                    @if ($column2->isNotEmpty())
                        <div class="flex w-[400px] max-w-full flex-col gap-6 max-sm:w-full max-md:gap-4">
                            @foreach ($column2 as $group)
                                @if ($group->custom_attributes->isNotEmpty())
                                    @include('b2b::shop.companies.account.profile.partials.group', [
                                        'group'    => $group,
                                        'customer' => $customer,
                                    ])
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                </fieldset>
            @endif

            {!! view_render_event('bagisto.shop.companies.account.profile.controls.after', ['customer' => $customer]) !!}

        </x-shop::form>

        {!! view_render_event('bagisto.shop.companies.account.profile.after', ['customer' => $customer]) !!}

    </div>
</x-shop::layouts.account>
