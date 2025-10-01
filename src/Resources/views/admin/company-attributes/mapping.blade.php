<x-admin::layouts>
    <x-slot:title>
        @lang('b2b_suite::app.admin.company-attributes.mapping.title')
    </x-slot>

    <!-- Input Form -->
    <x-admin::form :action="route('admin.customers.attributes.update_mapping')">
        <!-- Page Header -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('b2b_suite::app.admin.company-attributes.mapping.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.customers.attributes.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('b2b_suite::app.admin.company-attributes.mapping.back-btn')
                </a>

                <button 
                    type="submit" 
                    class="primary-button"
                >
                    @lang('b2b_suite::app.admin.company-attributes.mapping.save-btn')
                </button>
            </div>
        </div>

        <!-- Container -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Container -->
            <div class="box-shadow flex flex-1 flex-col gap-2 rounded bg-white dark:bg-gray-900 max-xl:flex-auto">
                <v-family-attributes>
                    <x-admin::shimmer.catalog.families.attributes-panel />
                </v-family-attributes>
            </div>
        </div>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-family-attributes-template"
        >
            <div class="">
                <!-- Panel Header -->
                <div class="mb-2.5 flex flex-wrap justify-between gap-2.5 p-4">
                    <!-- Panel Header -->
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('b2b_suite::app.admin.company-attributes.mapping.groups')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('b2b_suite::app.admin.company-attributes.mapping.groups-info')
                        </p>
                    </div>
                    
                    <!-- Panel Content -->
                    <div class="flex items-center gap-x-1">
                        <!-- Delete Group Button -->
                        <div
                            class="transparent-button text-red-600"
                            @click="deleteGroup"
                        >
                            @lang('b2b_suite::app.admin.company-attributes.mapping.delete-group-btn')
                        </div>

                        <!-- Add Group Button -->
                        <div
                            class="secondary-button"
                            @click="$refs.addGroupDrawer.open()"
                        >
                            @lang('b2b_suite::app.admin.company-attributes.mapping.add-group-btn')
                        </div>
                    </div>
                </div>

                <!-- Panel Content -->
                <div class="flex [&>*]:flex-1 gap-5 justify-between px-4">
                    <!-- Attributes Groups Container -->
                    <div v-for="(groups, column) in columnGroups">
                        <!-- Attributes Groups Header -->
                        <div class="mb-4 flex flex-col">
                            <p class="font-semibold leading-6 text-gray-600 dark:text-gray-300">
                                @{{
                                    column == 1
                                    ? "@lang('b2b_suite::app.admin.company-attributes.mapping.main-column')"
                                    : "@lang('b2b_suite::app.admin.company-attributes.mapping.right-column')"
                                }}
                            </p>
                            
                            <p class="text-xs font-medium text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.mapping.edit-group-info')
                            </p>
                        </div>

                        <!-- Draggable Attribute Groups -->
                        <draggable
                            class="h-[calc(100vh-285px)] overflow-auto border-gray-200 pb-4 ltr:border-r rtl:border-l"
                            ghost-class="draggable-ghost"
                            handle=".icon-drag"
                            v-bind="{animation: 200}"
                            :list="groups"
                            item-key="id"
                            group="groups"
                        >
                            <template #item="{ element, index }">
                                <div class="">
                                    <!-- Group Container -->
                                    <div class="group flex items-center">
                                        <!-- Toggle -->
                                        <i
                                            class="icon-sort-down cursor-pointer rounded-md text-xl transition-all hover:bg-gray-100 group-hover:text-gray-800 dark:hover:bg-gray-950 dark:group-hover:text-white"
                                            @click="element.hide = ! element.hide"
                                        ></i>

                                        <!-- Group Name -->
                                        <div
                                            class="group_node group flex max-w-max gap-1.5 rounded py-1.5 text-gray-600 transition-all dark:text-gray-300 ltr:pr-1.5 rtl:pl-1.5"
                                            :class="{'bg-blue-600 text-white group-hover:[&>*]:text-white': selectedGroup.id == element.id}"
                                            @click.stop="groupSelected(element)"
                                        >
                                            <i class="icon-drag cursor-grab text-xl text-inherit transition-all group-hover:text-gray-800 dark:group-hover:text-white"></i>

                                            <i
                                                class="text-xl text-inherit transition-all group-hover:text-gray-800 dark:group-hover:text-white"
                                                :class="[element.is_user_defined ? 'icon-folder' : 'icon-folder-block']"
                                            ></i>

                                            <span
                                                class="font-regular text-sm text-inherit transition-all group-hover:text-gray-800 dark:group-hover:text-white"
                                                v-show="editableGroup.id != element.id"
                                            >
                                                @{{ element.admin_name }}
                                            </span>

                                            <input
                                                type="hidden"
                                                :name="'attribute_groups[' + element.id + '][code]'"
                                                :value="element.code"
                                            />
                                            
                                            <div
                                                v-show="editableGroup.id == element.id" 
                                                class="flex flex-col gap-2.5 rounded border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800"
                                            >                                                
                                                <!-- Admin Name -->
                                                <input
                                                    type="text"
                                                    :name="'attribute_groups[' + element.id + '][admin_name]'"
                                                    v-model="element.admin_name"
                                                    class="group_node w-full rounded border border-gray-300 bg-gray-50 px-2.5 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                    placeholder="@lang('b2b_suite::app.admin.company-attributes.mapping.admin-name')"
                                                />

                                                <!-- Localized Names -->
                                                <div
                                                    v-for="locale in locales"
                                                    :key="locale.code"
                                                    class="relative"
                                                >
                                                    <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[11px] uppercase text-gray-400">
                                                        @{{ locale.code }}
                                                    </span>

                                                    <input
                                                        type="text"
                                                        :name="'attribute_groups[' + element.id + '][locales][' + locale.code + ']'"
                                                        :value="getTranslationName(element, locale.code)"
                                                        @input="updateTranslationName(element,locale.code, $event.target.value)"
                                                        class="group_node w-full rounded-md border border-gray-300 bg-gray-50 py-1.5 pl-9 pr-2 text-sm text-gray-700 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                                        :placeholder="locale.name"
                                                    />
                                                </div>
                                            </div>

                                            <input
                                                type="hidden"
                                                :name="'attribute_groups[' + element.id + '][position]'"
                                                :value="index + 1"
                                            />

                                            <input
                                                type="hidden"
                                                :name="'attribute_groups[' + element.id + '][column]'"
                                                :value="column"
                                            />
                                        </div>
                                    </div>

                                    <!-- Group Attributes -->
                                    <draggable
                                        class="ltr:ml-11 rtl:mr-11"
                                        ghost-class="draggable-ghost"
                                        handle=".icon-drag"
                                        v-bind="{animation: 200}"
                                        :list="getGroupAttributes(element)"
                                        item-key="id"
                                        group="attributes"
                                        :move="onMove"
                                        @end="onEnd"
                                        v-show="! element.hide"
                                    >
                                        <template #item="{ element, index }">
                                            <div class="group flex max-w-max gap-1.5 rounded py-1.5 text-gray-600 dark:text-gray-300 ltr:pr-1.5 rtl:pl-1.5">
                                                <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-800 dark:group-hover:text-white"></i>

                                                <i
                                                    class="text-xl transition-all group-hover:text-gray-800 dark:group-hover:text-white"
                                                    :class="[parseInt(element.is_user_defined) ? 'icon-attribute' : 'icon-attribute-block']"
                                                ></i>
                                                
                                                <span class="font-regular text-sm transition-all group-hover:text-gray-800 dark:group-hover:text-white max-xl:text-xs">
                                                    @{{ element.admin_name }}
                                                </span>

                                                <input
                                                    type="hidden"
                                                    :name="'attribute_groups[' + element.group_id + '][custom_attributes][' + index + '][id]'"
                                                    class="text-sm text-gray-600 dark:text-gray-300"
                                                    v-model="element.id"
                                                />

                                                <input
                                                    type="hidden"
                                                    :name="'attribute_groups[' + element.group_id + '][custom_attributes][' + index + '][position]'"
                                                    class="text-sm text-gray-600 dark:text-gray-300"
                                                    :value="index + 1"
                                                />
                                            </div>
                                        </template>
                                    </draggable>
                                </div>
                            </template>
                        </draggable>
                    </div>

                    <!-- Unassigned Attributes Container -->
                    <div class="">
                        <!-- Unassigned Attributes Header -->
                        <div class="mb-4 flex flex-col">
                            <p class="font-semibold leading-6 text-gray-600 dark:text-gray-300">
                                @lang('b2b_suite::app.admin.company-attributes.mapping.unassigned-attributes')
                            </p>

                            <p class="text-xs font-medium text-gray-800 dark:text-white">
                                @lang('b2b_suite::app.admin.company-attributes.mapping.unassigned-attributes-info')
                            </p>
                        </div>

                        <!-- Draggable Unassigned Attributes -->
                        <draggable
                            id="unassigned-attributes"
                            class="h-[calc(100vh-285px)] overflow-auto pb-4"
                            ghost-class="draggable-ghost"
                            handle=".icon-drag"
                            v-bind="{animation: 200}"
                            :list="unassignedAttributes"
                            item-key="id"
                            group="attributes"
                        >
                            <template #item="{ element }">
                                <div class="group flex max-w-max gap-1.5 rounded py-1.5 text-gray-600 dark:text-gray-300 ltr:pr-1.5 rtl:pl-1.5">
                                    <i class="icon-drag cursor-grab text-xl transition-all group-hover:text-gray-800 dark:group-hover:text-white"></i>

                                    <i class="icon-attribute text-xl transition-all group-hover:text-gray-800 dark:group-hover:text-white"></i>

                                    <span class="font-regular text-sm transition-all group-hover:text-gray-800 dark:group-hover:text-white max-xl:text-xs">
                                        @{{ element.admin_name }}
                                    </span>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>

                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form @submit="handleSubmit($event, addGroup)">
                        <x-admin::drawer ref="addGroupDrawer">
                            <!-- Drawer Header -->
                            <x-slot:header>
                                <div class="flex items-center justify-between">
                                    <p class="text-xl font-medium dark:text-white">
                                        @lang('b2b_suite::app.admin.company-attributes.mapping.add-group-title')
                                    </p>

                                    <button
                                        class="primary-button ltr:mr-11 rtl:ml-11"
                                        type="submit"
                                    >
                                        @lang('b2b_suite::app.admin.company-attributes.mapping.add-group-btn')
                                    </button>
                                </div>
                            </x-slot>

                            <!-- Drawer Content -->
                            <x-slot:content class="p-4">
                                <div class="flex gap-2.5">
                                    <x-admin::form.control-group class="flex-1">
                                        <x-admin::form.control-group.label class="required">
                                            @lang('b2b_suite::app.admin.company-attributes.mapping.code')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            name="code"
                                            rules="required"
                                            :label="trans('b2b_suite::app.admin.company-attributes.mapping.code')"
                                            :placeholder="trans('b2b_suite::app.admin.company-attributes.mapping.code')"
                                        />

                                        <x-admin::form.control-group.error control-name="code" />
                                    </x-admin::form.control-group>

                                    <x-admin::form.control-group class="flex-1">
                                        <x-admin::form.control-group.label class="required">
                                            @lang('b2b_suite::app.admin.company-attributes.mapping.column')
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="select"
                                            name="column"
                                            rules="required"
                                            :label="trans('b2b_suite::app.admin.company-attributes.mapping.column')"
                                        >
                                            <!-- Default Option -->
                                            <option value="">
                                                @lang('b2b_suite::app.admin.company-attributes.mapping.select-group')
                                            </option>

                                            <option value="1">
                                                @lang('b2b_suite::app.admin.company-attributes.mapping.main-column')
                                            </option>

                                            <option value="2">
                                                @lang('b2b_suite::app.admin.company-attributes.mapping.right-column')
                                            </option>
                                        </x-admin::form.control-group.control>

                                        <x-admin::form.control-group.error control-name="column" />
                                    </x-admin::form.control-group>
                                </div>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('b2b_suite::app.admin.company-attributes.mapping.admin-name')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="admin_name"
                                        rules="required"
                                        :label="trans('b2b_suite::app.admin.company-attributes.mapping.admin-name')"
                                        :placeholder="trans('b2b_suite::app.admin.company-attributes.mapping.admin-name')"
                                    />

                                    <x-admin::form.control-group.error control-name="name" />
                                </x-admin::form.control-group>

                                <!-- Locales Inputs -->
                                <template v-for="locale in locales">
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @{{ locale.name }} (@{{ locale.code.toUpperCase() }})
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            ::name="locale.code"
                                            ::placeholder="locale.name"
                                        />
                                    </x-admin::form.control-group>
                                </template>
                            </x-slot>
                        </x-admin::drawer>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-family-attributes', {
                template: '#v-family-attributes-template',

                data () {
                    return {
                        selectedGroup: {
                            id: null,
                            code: null,
                            admin_name: null,
                            translations: [],
                        },

                        editableGroup: {
                            id: null,
                            code: null,
                            admin_name: null,
                            translations: [],
                        },

                        columnGroups: @json($attributeGroups->groupBy('column')),

                        customAttributes: @json($customAttributes),

                        locales: @json($locales ?? []),

                        dropReverted: false,
                    }
                },

                created() {
                    // Ensure locales is always an array with proper structure
                    if (!Array.isArray(this.locales)) {
                        this.locales = [];
                    }
                    
                    // Validate locale structure
                    this.locales = this.locales.filter(locale => {
                        const isValid = locale && 
                            typeof locale === 'object' && 
                            locale.code && 
                            locale.name;
                        
                        if (!isValid) {
                            console.warn('Invalid locale structure:', locale);
                        }
                        
                        return isValid;
                    });
                    
                    window.addEventListener('click', this.handleFocusOut);
                },

                computed: {
                    unassignedAttributes() {
                        this.columnGroups[1] = this.columnGroups[1] || [];
                        this.columnGroups[2] = this.columnGroups[2] || [];

                        return this.customAttributes.filter(attribute => {
                            const isInGroup1 = this.columnGroups[1].some(group => 
                                group.custom_attributes.some(customAttribute => customAttribute.id === attribute.id)
                            );

                            const isInGroup2 = this.columnGroups[2].some(group => 
                                group.custom_attributes.some(customAttribute => customAttribute.id === attribute.id)
                            );

                            return !isInGroup1 && !isInGroup2;
                        });
                    },
                },

                methods: {
                    onMove: function(e) {
                        if (
                            e.to.id === 'unassigned-attributes'
                            && ! e.draggedContext.element.is_user_defined
                        ) {
                            this.dropReverted = true;

                            return false;
                        } else {
                            this.dropReverted = false;
                        }
                    },

                    onEnd: function(e) {
                        if (this.dropReverted) {
                            this.$emitter.emit('add-flash', {
                                type: 'warning',
                                message: "@lang('b2b_suite::app.admin.company-attributes.mapping.removal-not-possible')"
                            });
                        }
                    },

                    getGroupAttributes(group) {
                        group.custom_attributes.forEach((attribute, index) => {
                            attribute.group_id = group.id;
                        });

                        return group.custom_attributes;
                    },

                    groupSelected(group) {
                        if (this.selectedGroup.id) {
                            this.editableGroup = this.selectedGroup.id == group.id
                                ? group
                                : {
                                    id: null,
                                    code: null,
                                    admin_name: null,
                                    translations: [],
                                };
                        }

                        this.selectedGroup = group;
                    },

                    addGroup(params, { resetForm, setErrors }) {
                        let self = this;
                        
                        let isGroupCodeAlreadyExists = self.isGroupCodeAlreadyExists(params.code);

                        let isGroupNameAlreadyExists = self.isGroupNameAlreadyExists(params.admin_name);

                        if (isGroupCodeAlreadyExists || isGroupNameAlreadyExists) {
                            if (isGroupCodeAlreadyExists) {
                                setErrors({'code': [
                                    "@lang('b2b_suite::app.admin.company-attributes.mapping.group-code-already-exists')"
                                ]});
                            }

                            if (isGroupNameAlreadyExists) {
                                setErrors({'admin_name': [
                                    "@lang('b2b_suite::app.admin.company-attributes.mapping.group-name-already-exists')"
                                ]});
                            }

                            return;
                        }

                        this.columnGroups[params.column].push({
                            'id': 'group_' + params.column + '_' + this.columnGroups[params.column].length,
                            'code': params.code,
                            'admin_name': params.admin_name,
                            'translations': (self.locales && Array.isArray(self.locales)) ? self.locales.map(locale => ({
                                locale: locale.code,
                                name: params[locale.code] || '',
                            })) : [],
                            'locales': params.locales,
                            'is_user_defined': 1,
                            'custom_attributes': [],
                        });

                        resetForm();

                        this.$refs.addGroupDrawer.close();
                    },
                    
                    isGroupCodeAlreadyExists(code) {
                        return this.columnGroups[1].find(group => group.code == code) || this.columnGroups[2].find(group => group.code == code);
                    },
                    
                    isGroupNameAlreadyExists(name) {
                        return this.columnGroups[1].find(group => group.admin_name == name) || this.columnGroups[2].find(group => group.admin_name == name);
                    },
                    
                    isGroupContainsSystemAttributes(group) {
                        return group.custom_attributes.find(attribute => ! attribute.is_user_defined);
                    },

                    deleteGroup() {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: () => {
                                if (! this.selectedGroup.id) {
                                    this.$emitter.emit('add-flash', {
                                        type: 'warning',
                                        message: "@lang('b2b_suite::app.admin.company-attributes.mapping.select-group')"
                                    });

                                    return;
                                }

                                if (this.isGroupContainsSystemAttributes(this.selectedGroup)) {
                                    this.$emitter.emit('add-flash', {
                                        type: 'warning',
                                        message: "@lang('b2b_suite::app.admin.company-attributes.mapping.group-contains-system-attributes')"
                                    });

                                    return;
                                }

                                for (const [key, groups] of Object.entries(this.columnGroups)) {
                                    let index = groups.indexOf(this.selectedGroup);

                                    if (index > -1) {
                                        groups.splice(index, 1);
                                    }
                                }
                            }
                        });
                    },

                    handleFocusOut(e) {
                        if (! e.target.classList.contains('group_node')) {
                            this.editableGroup = {
                                id: null,
                                code: null,
                                admin_name: null,
                                translations: [],
                            };
                        }
                    },

                    getTranslationName(attributeGroups, localeCode) {
                        return attributeGroups.translations.find(t => t.locale === localeCode)?.name || '';
                    },

                    updateTranslationName(attributeGroups, localeCode, value) {
                        let translation = attributeGroups.translations.find(t => t.locale === localeCode);

                        if (translation) {
                            translation.name = value;
                        } else {
                            attributeGroups.translations.push({ locale: localeCode, name: value });
                        }
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
