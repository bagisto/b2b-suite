<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.companies.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.b2b.companies.dit.before', ['company' => $company]) !!}

    <!-- Input Form -->
    <x-admin::form
        :action="route('admin.b2b.companies.update', $company->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.b2b.companies.dit.edit_form_controls.before', ['company' => $company]) !!}

        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('b2b::app.admin.companies.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.b2b.companies.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b::app.admin.layouts.back-btn')
                </a>

                <!-- Update Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('b2b::app.admin.companies.edit.update-btn')
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Column -->
            <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                @include('b2b::admin.companies.sales-rep', ['admins' => $admins, 'company' => $company])

                @foreach($attributeGroups->where('column', 1) as $group)
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ $group->admin_name }}
                            </p>
                        </x-slot>

                        <x-slot:content>
                            @foreach($group->custom_attributes as $attribute)
                                @include('b2b::admin.companies.field-types', ['attribute' => $attribute, 'company' => $company])
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
                                :value="(string) (int) $company->status"
                                rules="required"
                                :label="trans('b2b::app.admin.companies.edit.status')"
                            >
                                <option value="1" {{ (int) $company->status === 1 ? 'selected' : '' }}>
                                    @lang('b2b::app.admin.companies.edit.active')
                                </option>

                                <option value="0" {{ (int) $company->status === 0 ? 'selected' : '' }}>
                                    @lang('b2b::app.admin.companies.edit.pending')
                                </option>
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
                                @include('b2b::admin.companies.field-types', ['attribute' => $attribute, 'company' => $company])
                            @endforeach
                        </x-slot>
                    </x-admin::accordion>
                @endforeach
            </div>
        </div>

        {!! view_render_event('bagisto.admin.b2b.companies.dit.edit_form_controls.after', ['company' => $company]) !!}
    </x-admin::form>

    {!! view_render_event('bagisto.admin.b2b.companies.dit.after', ['company' => $company]) !!}
</x-admin::layouts>
