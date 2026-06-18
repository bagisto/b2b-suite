<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.b2b.companies.create.before') !!}

    <!-- Input Form -->
    <x-admin::form
        :action="route('admin.b2b.companies.store')"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.b2b.companies.create.create_form_controls.before') !!}

        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('b2b::app.admin.companies.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.b2b.companies.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b::app.admin.layouts.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('b2b::app.admin.companies.create.save-btn')
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Column -->
            <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                @include('b2b::admin.companies.sales-rep', ['admins' => $admins])

                @foreach($attributeGroups->where('column', 1) as $group)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $group->admin_name }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            @foreach($group->custom_attributes as $attribute)
                                @include('b2b::admin.companies.field-types', ['attribute' => $attribute])
                            @endforeach
                        </x-slot>
                    </x-admin::accordion>
                @endforeach
            </div>

            <!-- Right Column -->
            <div class="flex w-[360px] max-w-full flex-col gap-2">
                <!-- Approval / Status -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b::app.admin.companies.edit.approval-status')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('b2b::app.admin.companies.edit.status')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="status"
                                :value="old('status', '1')"
                                rules="required"
                                :label="trans('b2b::app.admin.companies.edit.status')"
                            >
                                <option value="1">@lang('b2b::app.admin.companies.edit.active')</option>

                                <option value="0">@lang('b2b::app.admin.companies.edit.pending')</option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="status" />
                        </x-admin::form.control-group>
                    </x-slot>
                </x-admin::accordion>

                @foreach($attributeGroups->where('column', 2) as $group)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $group->admin_name }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            @foreach($group->custom_attributes as $attribute)
                                @include('b2b::admin.companies.field-types', ['attribute' => $attribute])
                            @endforeach
                        </x-slot>
                    </x-admin::accordion>
                @endforeach
            </div>
        </div>

        {!! view_render_event('bagisto.admin.b2b.companies.create.create_form_controls.after') !!}
    </x-admin::form>

    {!! view_render_event('bagisto.admin.b2b.companies.create.after') !!}
</x-admin::layouts>
