@php
    $name = $attribute->code;

    if (isset($company)) {
        $value = old($name) ?: $company[$name];
    } else {
        $value = old($name, '');
    }
@endphp

@switch($attribute->type)
    @case('text')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="text"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
                :placeholder="$attribute->admin_name"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('textarea')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="textarea"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
                :placeholder="$attribute->admin_name"
                :tinymce="(bool) $attribute->enable_editor"
                rows="4"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('select')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
            >
                <option value="">@lang('admin::app.admin.system.select')</option>

                @php
                    $selectedOption = $value;

                    if ($attribute->code != 'tax_category_id') {
                        $options = $attribute->options()->orderBy('sort_order')->get();
                    } else {
                        $options = app('Webkul\Tax\Repositories\TaxCategoryRepository')->all();
                    }
                @endphp

                @foreach ($options as $option)
                    <option
                        value="{{ $option->id }}"
                        {{ $selectedOption == $option->id ? 'selected' : '' }}
                    >
                        {{ $option->admin_name ?? $option->name }}
                    </option>
                @endforeach
            </x-admin::form.control-group.control>

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('multiselect')
        @php
            $selectedOption = old($attribute->code) ?: explode(',', $company[$attribute->code]);
        @endphp
        
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <select 
                name="{{ $name }}[]"
                multiple
                class="control custom-select"
                {{ $attribute->is_required ? 'required' : '' }}
            >
                @foreach ($attribute->options()->orderBy('sort_order')->get() as $option)
                    <option
                        value="{{ $option->id }}"
                        {{ in_array($option->id, $selectedOption) ? 'selected' : ''}}
                    >
                        {{ $option->admin_name }}
                    </option>
                @endforeach
            </select>

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('checkbox')
        @php
            $selectedOption = old($attribute->code) ?: explode(',', $company[$attribute->code]);
        @endphp

        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label>
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            @if($attribute->options && $attribute->options->count())
                @php
                    $selectedValues = is_array($value) ? $value : explode(',', $value);
                @endphp
                @foreach($attribute->options as $option)
                    <div class="flex items-center gap-1.5">
                        <x-admin::form.control-group.control
                            type="checkbox"
                            :id="$name . '_' . $option->id"
                            :name="$name . '[]'"
                            :value="$option->admin_name"
                            :checked="in_array($option->id, $selectedOption)"
                        />
                        
                        <label class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300" for="{{ $name }}_{{ $option->id }}">
                            {{ $option->admin_name }}
                        </label>
                    </div>
                @endforeach
            @else
                <x-admin::form.control-group.control
                    type="checkbox"
                    :name="$name"
                    value="1"
                    :checked="$value"
                />
            @endif

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('boolean')
        @php $selectedValue = $value @endphp

        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="select"
                :id="$attribute->code"
                :name="$name"
                :value="$selectedValue"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
            >
                <option value="">@lang('admin::app.admin.system.select')</option>
                <option value="1" {{ $value == '1' ? 'selected' : '' }}>@lang('admin::app.admin.system.yes')</option>
                <option value="0" {{ $value == '0' ? 'selected' : '' }}>@lang('admin::app.admin.system.no')</option>
            </x-admin::form.control-group.control>

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('date')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="date"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('datetime')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="datetime-local"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('file')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required && !isset($company) ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="file"
                :id="$attribute->code"
                :name="$name"
                :rules="$attribute->is_required && !isset($company) ? 'required' : ''"
                :label="$attribute->admin_name"
            />

            @if($value && isset($company))
                <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                    <p>@lang('b2b_suite::app.admin.companies.current-file'): {{ $value }}</p>
                </div>
            @endif

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('image')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required && !isset($company) ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="file"
                :id="$attribute->code"
                :name="$name"
                :rules="$attribute->is_required && !isset($company) ? 'required' : ''"
                :label="$attribute->admin_name"
                accept="image/*"
            />

            @if($value && isset($company))
                <div class="mt-2">
                    <img src="{{ Storage::url($value) }}" alt="{{ $attribute->admin_name }}" class="h-20 w-20 rounded border">
                    <p class="text-xs text-gray-600 dark:text-gray-300">@lang('b2b_suite::app.admin.companies.current-image')</p>
                </div>
            @endif

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @case('price')
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="number"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required|decimal' : 'decimal'"
                :label="$attribute->admin_name"
                step="0.01"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
        @break

    @default
        <x-admin::form.control-group class="mb-2.5">
            <x-admin::form.control-group.label class="{{ $attribute->is_required ? 'required' : '' }}">
                {{ $attribute->admin_name }}
            </x-admin::form.control-group.label>

            <x-admin::form.control-group.control
                type="text"
                :id="$attribute->code"
                :name="$name"
                value="{{ $value }}"
                :rules="$attribute->is_required ? 'required' : ''"
                :label="$attribute->admin_name"
                :placeholder="$attribute->admin_name"
            />

            <x-admin::form.control-group.error :control-name="$name" />
        </x-admin::form.control-group>
@endswitch
