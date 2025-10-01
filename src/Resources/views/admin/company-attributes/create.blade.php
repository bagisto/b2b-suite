<x-admin::layouts>
    <x-slot:title>
        @lang('b2b_suite::app.admin.company-attributes.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.customers.attributes.create.before') !!}

    <!-- Input Form -->
    <x-admin::form
        :action="route('admin.customers.attributes.store')"
        enctype="multipart/form-data"
    >
        {!! view_render_event('bagisto.admin.customers.attributes.create.create_form_controls.before') !!}

        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('b2b_suite::app.admin.company-attributes.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.customers.attributes.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b_suite::app.admin.company-attributes.create.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('b2b_suite::app.admin.company-attributes.create.save-btn')
                </button>
            </div>
        </div>

        <!-- Create Attributes Vue Components -->
        <v-create-attributes>
            <!-- Shimmer Effect -->
            <x-admin::shimmer.catalog.attributes />
        </v-create-attributes>

        {!! view_render_event('bagisto.admin.customers.attributes.create_form_controls.after') !!}
    </x-admin::form>

    {!! view_render_event('bagisto.admin.customers.attributes.create.after') !!}

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-create-attributes-template"
        >
            <!-- Body Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">

                {!! view_render_event('bagisto.admin.customers.attributes.create.card.label.before') !!}

                <!-- Left Sub Component -->
                <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                    <!-- Label -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b_suite::app.admin.company-attributes.create.label')
                        </p>

                        <!-- Admin Name -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('b2b_suite::app.admin.company-attributes.create.admin')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="admin_name"
                                rules="required"
                                :value="old('admin_name')"
                                :label="trans('b2b_suite::app.admin.company-attributes.create.admin')"
                                :placeholder="trans('b2b_suite::app.admin.company-attributes.create.admin')"
                            />

                            <x-admin::form.control-group.error control-name="admin_name" />
                        </x-admin::form.control-group>

                        <!-- Locales Inputs -->
                        @foreach ($locales as $locale)
                            <x-admin::form.control-group class="last:!mb-0">
                                <x-admin::form.control-group.label>
                                    {{ $locale->name . ' (' . strtoupper($locale->code) . ')' }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    :name="$locale->code . '[name]'"
                                    :value="old($locale->code . '[name]')"
                                    :placeholder="$locale->name"
                                />
                            </x-admin::form.control-group>
                        @endforeach
                    </div>

                    <!-- Options -->
                    <div
                        class="box-shadow rounded bg-white p-4 dark:bg-gray-900"
                        v-if="swatchAttribute && (
                            attributeType == 'select'
                            || attributeType == 'multiselect'
                            || attributeType == 'checkbox'
                        )"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.create.options')
                            </p>

                            <!-- Add Row Button -->
                            <div
                                class="secondary-button text-sm"
                                @click="$refs.addOptionsRow.toggle()"
                            >
                                @lang('b2b_suite::app.admin.company-attributes.create.add-row')
                            </div>
                        </div>

                        <!-- For Attribute Options If Data Exist -->
                        <div class="mt-4 overflow-x-auto">
                            <div
                                class="flex gap-4 max-sm:flex-wrap"
                                v-if="swatchAttribute && (attributeType == 'select')"
                            >
                                <div class="mb-2.5 w-full">
                                    <!-- Checkbox -->
                                    <x-admin::form.control-group.label class="invisible">
                                        @lang('b2b_suite::app.admin.company-attributes.create.input-options')
                                    </x-admin::form.control-group.label>

                                    <div class="!mb-0 flex w-max cursor-pointer select-none items-center gap-2.5 p-1.5">
                                        <input
                                            type="checkbox"
                                            class="peer hidden"
                                            id="empty_option"
                                            name="empty_option"
                                            v-model="isNullOptionChecked"
                                            for="empty_option"
                                            @click="$refs.addOptionsRow.toggle()"
                                        />

                                        <label
                                            for="empty_option"
                                            class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                        >
                                        </label>

                                        <label
                                            for="empty_option"
                                            class="cursor-pointer text-sm font-semibold text-gray-600 dark:text-gray-300"
                                        >
                                            @lang('b2b_suite::app.admin.company-attributes.create.create-empty-option')
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <template v-if="this.options?.length">
                                <!-- Table Information -->
                                <x-admin::table>
                                    <x-admin::table.thead class="text-sm font-medium dark:bg-gray-800">
                                        <x-admin::table.thead.tr>
                                            <!-- Draggable Icon -->
                                            <x-admin::table.th class="!p-0" />

                                            <!-- Admin Tables Heading -->
                                            <x-admin::table.th>
                                                @lang('b2b_suite::app.admin.company-attributes.create.admin-name')
                                            </x-admin::table.th>

                                            <!-- Locales Tables Heading -->
                                            @foreach ($locales as $locale)
                                                <x-admin::table.th>
                                                    {{ $locale->name . ' (' . $locale->code . ')' }}
                                                </x-admin::table.th>
                                            @endforeach

                                            <!-- Action Tables Heading -->
                                            <x-admin::table.th />
                                        </x-admin::table.thead.tr>
                                    </x-admin::table.thead>

                                    <!-- Draggable Component -->
                                    <draggable
                                        tag="tbody"
                                        ghost-class="draggable-ghost"
                                        handle=".icon-drag"
                                        v-bind="{animation: 200}"
                                        :list="options"
                                        item-key="id"
                                    >
                                        <template #item="{ element, index }">
                                            <x-admin::table.thead.tr class="hover:bg-gray-50 dark:hover:bg-gray-950">
                                                <!-- Draggable Icon -->
                                                <x-admin::table.td class="!px-0 text-center">
                                                    <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-700"></i>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][position]'"
                                                        :value="index"
                                                    />
                                                </x-admin::table.td>

                                                <!-- Admin -->
                                                <x-admin::table.td>
                                                    <p class="dark:text-white">
                                                        @{{ element.params.admin_name }}
                                                    </p>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][admin_name]'"
                                                        v-model="element.params.admin_name"
                                                    />
                                                </x-admin::table.td>

                                                <!-- Locales -->
                                                <x-admin::table.td v-for="locale in locales">
                                                    <p class="dark:text-white">
                                                        @{{ element.params[locale.code] }}
                                                    </p>

                                                    <input
                                                        type="hidden"
                                                        :name="'options[' + element.id + '][' + locale.code + '][label]'"
                                                        v-model="element.params[locale.code]"
                                                    />
                                                </x-admin::table.td>

                                                <!-- Action Buttons -->
                                                <x-admin::table.td class="!px-0">
                                                    <span
                                                        class="icon-edit cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                        @click="editModal(element)"
                                                    >
                                                    </span>

                                                    <span
                                                        class="icon-delete cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800 max-sm:place-self-center"
                                                        @click="removeOption(element.id)"
                                                    >
                                                    </span>
                                                </x-admin::table.td>
                                            </x-admin::table.thead.tr>
                                        </template>
                                    </draggable>
                                </x-admin::table>
                            </template>

                            <!-- For Empty Attribute Options -->
                            <template v-else>
                                <div class="grid justify-items-center gap-3.5 px-2.5 py-10">
                                    <!-- Attribute Option Image -->
                                    <img
                                        class="h-[120px] w-[120px] dark:mix-blend-exclusion dark:invert"
                                        src="{{ bagisto_asset('images/icon-add-product.svg') }}"
                                        alt="@lang('b2b_suite::app.admin.company-attributes.create.add-attribute-options')"
                                    />

                                    <!-- Add Attribute Options Information -->
                                    <div class="flex flex-col items-center gap-1.5">
                                        <p class="text-base font-semibold text-gray-400">
                                            @lang('b2b_suite::app.admin.company-attributes.create.add-attribute-options')
                                        </p>

                                        <p class="text-gray-400">
                                            @lang('b2b_suite::app.admin.company-attributes.create.add-options-info')
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {!! view_render_event('bagisto.admin.customers.attributes.create.card.label.after') !!}

                {!! view_render_event('bagisto.admin.customers.attributes.create.card.general.before') !!}

                <!-- Right Sub Component -->
                <div class="flex w-[360px] max-w-full flex-col gap-2">
                    <!-- General -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.create.general')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Attribute Code -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('b2b_suite::app.admin.company-attributes.create.code')
                                </x-admin::form.control-group.label>

                                <v-field
                                    type="text"
                                    name="code"
                                    rules="required"
                                    value="{{ old('code') }}"
                                    v-slot="{ field }"
                                    label="{{ trans('b2b_suite::app.admin.company-attributes.create.code') }}"
                                >
                                    <input
                                        type="text"
                                        id="code"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        name="code"
                                        v-bind="field"
                                        placeholder="{{ trans('b2b_suite::app.admin.company-attributes.create.code') }}"
                                    />
                                </v-field>

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <!-- Attribute Type -->
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('b2b_suite::app.admin.company-attributes.create.type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    id="type"
                                    class="cursor-pointer"
                                    name="type"
                                    rules="required"
                                    :value="old('type')"
                                    v-model="attributeType"
                                    :label="trans('b2b_suite::app.admin.company-attributes.create.type')"
                                    @change="swatchAttribute=true"
                                >
                                    @foreach($attributeTypes as $attributeType)
                                        <option
                                            value="{{ $attributeType }}"
                                            {{ $attributeType === 'text' ? "selected" : '' }}
                                        >
                                            @lang('b2b_suite::app.admin.company-attributes.create.'. $attributeType)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            <!-- Textarea Switcher -->
                            <x-admin::form.control-group v-show="swatchAttribute && (attributeType == 'textarea')">
                                <x-admin::form.control-group.label>
                                    @lang('b2b_suite::app.admin.company-attributes.create.enable-editor')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="switch"
                                    name="enable_editor"
                                    value="1"
                                    :label="trans('b2b_suite::app.admin.company-attributes.create.enable-editor')"
                                />
                            </x-admin::form.control-group>

                            <!-- Default Value -->
                            <x-admin::form.control-group
                                class="!mb-0"
                                v-if="canHaveDefaultValue"
                            >
                                <x-admin::form.control-group.label>
                                    @lang('b2b_suite::app.admin.company-attributes.create.default-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="default_value"
                                    :label="trans('b2b_suite::app.admin.company-attributes.create.default-value')"
                                />

                                <x-admin::form.control-group.error control-name="default_value" />
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>

                    <!-- Validations -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.create.validations')
                            </p>
                        </x-slot>

                        <x-slot:content>
                            <!-- Input Validation -->
                            <x-admin::form.control-group v-if="swatchAttribute && (attributeType == 'text')">
                                <x-admin::form.control-group.label>
                                    @lang('b2b_suite::app.admin.company-attributes.create.input-validation')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    class="cursor-pointer"
                                    id="validation"
                                    name="validation"
                                    :value="old('validation')"
                                    v-model="validationType"
                                    :label="trans('b2b_suite::app.admin.company-attributes.create.input-validation')"
                                    refs="validation"
                                    @change="inputValidation=true"
                                >
                                    @foreach($validations as $validation)
                                        <option value="{{ $validation }}">
                                            @lang('b2b_suite::app.admin.company-attributes.create.' . $validation)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="validation" />
                            </x-admin::form.control-group>

                            <!-- REGEX -->
                            <x-admin::form.control-group v-show="inputValidation && (validationType == 'regex')">
                                <x-admin::form.control-group.label>
                                    @lang('b2b_suite::app.admin.company-attributes.create.regex')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="regex"
                                    :value="old('regex')"
                                    :placeholder="trans('b2b_suite::app.admin.company-attributes.create.regex')"
                                />

                                <x-admin::form.control-group.error control-name="regex" />
                            </x-admin::form.control-group>

                            <!-- Is Required -->
                                <x-admin::form.control-group class="!mb-2 flex items-center gap-2.5">
                                <x-admin::form.control-group.control
                                    type="checkbox"
                                    id="is_required"
                                    name="is_required"
                                    value="1"
                                    for="is_required"
                                />

                                <label
                                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                    for="is_required"
                                >
                                    @lang('b2b_suite::app.admin.company-attributes.edit.is-required')
                                </label>
                            </x-admin::form.control-group>

                            <!-- Is Unique -->
                            <x-admin::form.control-group class="!mb-0 flex select-none items-center gap-2.5">
                                <x-admin::form.control-group.control
                                    type="checkbox"
                                    id="is_unique"
                                    name="is_unique"
                                    value="1"
                                    for="is_unique"
                                />

                                <label
                                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                    for="is_unique"
                                >
                                    @lang('b2b_suite::app.admin.company-attributes.edit.is-unique')
                                </label>
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>

                    <!-- Configurations -->
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.create.configuration')
                            </p>
                        </x-slot>

                            <x-slot:content>
                                <!-- Value Per Locale -->
                                <x-admin::form.control-group class="!mb-2 flex select-none items-center gap-2.5">
                                    <x-admin::form.control-group.control
                                        type="checkbox"
                                        id="value_per_locale"
                                        name="value_per_locale"
                                        value="1"
                                        for="value_per_locale"
                                    />

                                    <label
                                        class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                        for="value_per_locale"
                                    >
                                        @lang('b2b_suite::app.admin.company-attributes.edit.value-per-locale')
                                    </label>
                                </x-admin::form.control-group>

                            <!-- Value Per Channel -->
                            <x-admin::form.control-group class="!mb-2 flex select-none items-center gap-2.5">
                                <x-admin::form.control-group.control
                                    type="checkbox"
                                    id="value_per_channel"
                                    name="value_per_channel"
                                    value="1"
                                    for="value_per_channel"
                                />

                                <label
                                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                    for="value_per_channel"
                                >
                                    @lang('b2b_suite::app.admin.company-attributes.edit.value-per-channel')
                                </label>
                            </x-admin::form.control-group>

                            <!-- Visible On Product View Page On Front End -->
                            <x-admin::form.control-group class="!mb-2 flex select-none items-center gap-2.5">
                                <x-admin::form.control-group.control
                                    type="checkbox"
                                    id="is_visible_on_sign_up"
                                    name="is_visible_on_sign_up"
                                    value="1"
                                    for="is_visible_on_sign_up"
                                />

                                <label
                                    class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                    for="is_visible_on_sign_up"
                                >
                                    @lang('b2b_suite::app.admin.company-attributes.create.is-visible-on-sign-up')
                                </label>
                            </x-admin::form.control-group>
                        </x-slot>
                    </x-admin::accordion>
                </div>

                {!! view_render_event('bagisto.admin.customers.attributes.create.card.general.after') !!}

            </div>

            <!-- Add Options Model Form -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
                ref="modelForm"
            >
                <form
                    @submit.prevent="handleSubmit($event, storeOptions)"
                    enctype="multipart/form-data"
                    ref="createOptionsForm"
                >
                    <x-admin::modal
                        @toggle="listenModal"
                        ref="addOptionsRow"
                    >
                        <!-- Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.create.add-option')
                            </p>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content>
                            <div class="grid grid-cols-3 gap-4">
                                <!-- Hidden Id Input -->
                                <x-admin::form.control-group.control
                                    type="hidden"
                                    name="id"
                                />

                                <!-- Admin Input -->
                                <x-admin::form.control-group class="!mb-2.5 w-full">
                                    <x-admin::form.control-group.label ::class="{ 'required' : ! isNullOptionChecked }">
                                        @lang('b2b_suite::app.admin.company-attributes.create.admin')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="admin_name"
                                        ::rules="{ 'required' : ! isNullOptionChecked }"
                                        :label="trans('b2b_suite::app.admin.company-attributes.create.admin')"
                                        :placeholder="trans('b2b_suite::app.admin.company-attributes.create.admin')"
                                    />

                                    <x-admin::form.control-group.error control-name="admin_name" />
                                </x-admin::form.control-group>

                                <!-- Locales Input -->
                                @foreach ($locales as $locale)
                                    <x-admin::form.control-group class="!mb-2.5 w-full">
                                        <x-admin::form.control-group.label ::class="{ '{{core()->getDefaultLocaleCodeFromDefaultChannel() == $locale->code ? 'required' : ''}}' : ! isNullOptionChecked }">
                                            {{ $locale->name }} ({{ strtoupper($locale->code) }})
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            :name="$locale->code"
                                            ::rules="{ '{{core()->getDefaultLocaleCodeFromDefaultChannel() == $locale->code ? 'required' : ''}}' : ! isNullOptionChecked }"
                                            :label="$locale->name"
                                            :placeholder="$locale->name"
                                        />

                                        <x-admin::form.control-group.error :control-name="$locale->code" />
                                    </x-admin::form.control-group>
                                @endforeach
                            </div>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('b2b_suite::app.admin.company-attributes.create.save-option')"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </script>

        <script type="module">
            app.component('v-create-attributes', {
                template: '#v-create-attributes-template',

                data() {
                    return {
                        optionRowCount: 1,

                        attributeType: '{{ old('type') }}',

                        validationType: '',

                        inputValidation: false,

                        swatchAttribute: false,

                        isNullOptionChecked: false,

                        options: [],

                        locales: @json($locales),
                    }
                },

                computed: {
                    canHaveDefaultValue() {
                        return this.attributeType == 'boolean';
                    },
                },

                methods: {
                    storeOptions(params, { resetForm }) {
                        const sortedLocales = Object.values(this.locales).sort((a, b) => a.name.localeCompare(b.name));

                        this.locales = sortedLocales.map(({ code, name }) => ({ code, name }));

                        const sortedParams = sortedLocales.reduce((acc, locale) => {
                            acc[locale.code] = params[locale.code] || null;
                            return acc;
                        }, {});

                        if (params.id) {
                            let foundIndex = this.options.findIndex(item => item.id === params.id);

                            if (foundIndex !== -1) {
                                Object.assign(this.options[foundIndex].params, sortedParams);
                            }
                        } else {
                            this.options.push({
                                id: `option_${this.optionRowCount}`,
                                params: { admin_name: params.admin_name, ...sortedParams }
                            });

                            params.id = `option_${this.optionRowCount}`;
                            this.optionRowCount++;
                        }

                        this.$refs.addOptionsRow.toggle();

                        resetForm();
                    },

                    editModal(values) {
                        values.params.id = values.id;

                        this.$refs.modelForm.setValues(values.params);

                        this.$refs.addOptionsRow.toggle();
                    },

                    removeOption(id) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                this.options = this.options.filter(option => option.id !== id);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('b2b_suite::app.admin.company-attributes.create.option-deleted')"
                                });
                            }
                        });
                    },

                    listenModal(event) {
                        if (! event.isActive) {
                            this.isNullOptionChecked = false;
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
