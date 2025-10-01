@switch($attribute->type)
    @case('text')
        <x-shop::form.control-group.control
            type="text"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            :name="$attribute->code"
            :id="$attribute->code"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
            value="{{ old($attribute->code) }}"
            aria-required="true"
        />
        @break

    @case('price')
        <div class="relative">
            <x-shop::form.control-group.control
                type="price"
                :name="$attribute->code"
                :id="$attribute->code"
                ::rules="{{ $attribute->validations }}"
                :label="$attribute->name"
                value="{{ old($attribute->code) }}"
                class="px-6 py-4 max-md:py-3 max-sm:py-2"
            >
                <x-slot:currency :class="'' . ($attribute->code == 'price' ? 'text-xl' : '')">
                    {{ core()->currencySymbol(core()->getBaseCurrencyCode()) }}
                </x-slot>
            </x-shop::form.control-group.control>
        </div>
    @break

    @case('textarea')
        <x-shop::form.control-group.control
            type="textarea"
            :name="$attribute->code"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            :id="$attribute->code"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
            value="{{ old($attribute->code) }}"
            :tinymce="(bool) $attribute->enable_editor"
        />

        @break

    @case('date')
        <x-shop::form.control-group.control
            type="date"
            :name="$attribute->code"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            :id="$attribute->code"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
            value="{{ old($attribute->code) }}"
        />

        @break

    @case('datetime')
        <x-shop::form.control-group.control
            type="datetime"
            :name="$attribute->code"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
            value="{{ old($attribute->code) }}"
        />

        @break

    @case('select')
        <x-shop::form.control-group.control
            type="select"
            :name="$attribute->code"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            :id="$attribute->code"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
            :value="old($attribute->code)"
        >
            @php
                $selectedOption = old($attribute->code);

                $options = $attribute->options()->orderBy('sort_order')->get();
            @endphp

            @foreach ($options as $option)
                <option
                    value="{{ $option->id }}"
                    {{ $selectedOption == $option->id ? 'selected' : '' }}
                >
                    {{ $option->name ?? $option->name }}
                </option>
            @endforeach
        </x-shop::form.control-group.control>

        @break

    @case('multiselect')
        @php
            $selectedOption = old($attribute->code);
        @endphp

        <x-shop::form.control-group.control
            type="multiselect"
            :name="$attribute->code.'[]'"
            class="px-6 py-4 max-md:py-3 max-sm:py-2"
            :id="$attribute->code.'[]'"
            ::rules="{{ $attribute->validations }}"
            :label="$attribute->name"
        >
            @foreach ($attribute->options()->orderBy('sort_order')->get() as $option)
                <option
                    value="{{ $option->id }}"
                    {{ in_array($option->id, $selectedOption) ? 'selected' : ''}}
                >
                    {{ $option->name }}
                </option>
            @endforeach
        </x-shop::form.control-group.control>

        @break

    @case('checkbox')
        @php
            $selectedOption = old($attribute->code);
        @endphp

        @foreach ($attribute->options as $option)
            <div class="flex items-center gap-2.5 py-1.5">
                <x-shop::form.control-group.control
                    type="checkbox"
                    :name="$attribute->code.'[]'"
                    :value="$option->id"
                    :id="$attribute->code.'_'.$option->id"
                    :for="$attribute->code.'_'.$option->id"
                    ::rules="{{ $attribute->validations }}"
                    :label="$attribute->name"
                    :checked="in_array($option->id, $selectedOption)"
                >
                </x-shop::form.control-group.control>

                <p class="font-semibold text-gray-600">
                    {{ $option->name }}
                </p>
            </div>
        @endforeach

        @break

    @case('boolean')
        @php $selectedValue = old($attribute->code) @endphp

        <x-shop::form.control-group.control
            type="switch"
            :name="$attribute->code"
            :id="$attribute->code"
            :value="1"
            :label="$attribute->name"
            :checked="(boolean) $selectedValue"
        />

        @break

    @case('image')
    @case('file')
        <div class="flex gap-2.5">
            <v-field
                type="text"
                class="w-full"
                name="{{ $attribute->code }}"
                :rules="{{ $attribute->validations }}"
                label="{{ $attribute->name }}"
                v-slot="{ handleChange, handleBlur }"
                value="{{ old($attribute->code) }}"
            >
                <input
                    type="file"
                    name="{{ $attribute->code }}"
                    id="{{ $attribute->code }}"
                    :class="[errors['{{ $attribute->code }}'] ? 'border border-red-600 hover:border-red-600' : '']"
                    class="min-h-10 flex w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400"
                    @change="handleChange"
                    @blur="handleBlur"
                >
            </v-field>
        </div>

        @break

@endswitch