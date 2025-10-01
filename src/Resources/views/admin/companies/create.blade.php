<x-admin::layouts>
    <x-slot:title>
        @lang('b2b_suite::app.admin.companies.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.customers.companies.create.before') !!}

    <!-- Input Form -->
    <x-admin::form
        :action="route('admin.customers.companies.store')"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.customers.companies.create.create_form_controls.before') !!}

        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('b2b_suite::app.admin.companies.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.customers.companies.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b_suite::app.admin.layouts.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('b2b_suite::app.admin.companies.create.save-btn')
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Column -->
            <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                @foreach($attributeGroups->where('column', 1) as $group)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $group->admin_name }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            @foreach($group->custom_attributes as $attribute)
                                @include('b2b_suite::admin.companies.field-types', ['attribute' => $attribute])
                            @endforeach
                        </x-slot>
                    </x-admin::accordion>
                @endforeach
            </div>

            <!-- Right Column -->
            <div class="flex w-[360px] max-w-full flex-col gap-2">
                @foreach($attributeGroups->where('column', 2) as $group)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $group->admin_name }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            @foreach($group->custom_attributes as $attribute)
                                @include('b2b_suite::admin.companies.field-types', ['attribute' => $attribute])
                            @endforeach
                        </x-slot>
                    </x-admin::accordion>
                @endforeach
            </div>
        </div>

        {!! view_render_event('bagisto.admin.customers.companies.create.create_form_controls.after') !!}
    </x-admin::form>

    {!! view_render_event('bagisto.admin.customers.companies.create.after') !!}
</x-admin::layouts>
