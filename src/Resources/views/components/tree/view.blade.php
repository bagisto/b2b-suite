@props([
    'inputType' => 'checkbox',
    'selectionType' => 'hierarchical',
])

@if ($inputType == 'checkbox')
    <!-- Tree Checkbox Component -->
    <x-b2b::tree.checkbox />
@else
    <!-- Tree Radio Component -->
    <x-b2b::tree.radio />
@endif

@pushOnce('styles')
    <style>
        /**
         * Hierarchy is shown with proper connector lines (the shop theme purges the icon
         * font the tree was relying on). Each child draws a vertical trunk segment plus a
         * horizontal tick into its row; the last child stops the trunk at its centre to
         * form a clean corner — so a childless top-level feature (Company Credit, Quick
         * Orders) clearly sits at the root, not under the feature above it.
         */
        .tree-children {
            position: relative;
            margin-inline-start: 1rem;
            padding-inline-start: 1rem;
        }

        .tree-children > div {
            position: relative;
        }

        /* Vertical trunk: full height for middle children, half (corner) for the last. */
        .tree-children > div::before {
            content: "";
            position: absolute;
            inset-inline-start: -1rem;
            top: 0;
            height: 100%;
            border-inline-start: 1px solid #d4d4d8;
        }

        .tree-children > div:last-child::before {
            height: 50%;
        }

        /* Horizontal tick from the trunk into the child row. */
        .tree-children > div::after {
            content: "";
            position: absolute;
            inset-inline-start: -1rem;
            top: 50%;
            width: 0.875rem;
            border-top: 1px solid #d4d4d8;
        }
    </style>
@endPushOnce

<v-tree-view
    {{ $attributes->except(['input-type', 'selection-type']) }}
    input-type="{{ $inputType }}"
    selection-type="{{ $selectionType }}"
>
    <x-b2b::shimmer.tree />
</v-tree-view>

@pushOnce('scripts')
    <script type="module">
        app.component('v-tree-view', {
            name: 'v-tree-view',

            template: `
                <div class="v-tree-container">
                    <div class="v-tree-item-wrapper">
                        <tree-item
                            v-for="(item, index) in formattedItems"
                            :key="index"
                            :item="item"
                            :level="1"
                            :collapse="collapse"
                            :input-type="inputType"
                            :name-field="nameField"
                            :value-field="valueField"
                            :id-field="idField"
                            :label-field="labelField"
                            :children-field="childrenField"
                            :fallback-locale="fallbackLocale"
                            @toggle-item="handleCheckbox"
                        />
                    </div>
                </div>
            `,

            props: {
                inputType: {
                    type: String,
                    required: false,
                    default: 'checkbox'
                },

                selectionType: {
                    type: String,
                    required: false,
                    default: 'hierarchical'
                },

                nameField: {
                    type: String,
                    required: false,
                    default: 'permissions'
                },

                valueField: {
                    type: String,
                    required: false,
                    default: 'value'
                },

                idField: {
                    type: String,
                    required: false,
                    default: 'id'
                },

                labelField: {
                    type: String,
                    required: false,
                    default: 'name'
                },

                childrenField: {
                    type: String,
                    required: false,
                    default: 'children'
                },

                items: {
                    type: [Array, String, Object],
                    required: false,
                    default: () => ([])
                },

                value: {
                    type: [Array, String, Object],
                    required: false,
                    default: () => ([])
                },

                fallbackLocale: {
                    type: String,
                    required: 'en',
                },

                collapse: {
                    type: Boolean,
                    required: false,
                    default: false
                },
            },

            data() {
                return {
                    formattedItems: null,
                    formattedValues: null,
                };
            },

            created() {
                this.formattedItems = this.getInitialFormattedItems();
                this.formattedValues = this.getInitialFormattedValues();
            },

            methods: {
                getInitialFormattedItems() {
                    return (typeof this.items == 'string')
                        ? JSON.parse(this.items)
                        : this.items;
                },

                getInitialFormattedValues() {
                    if (this.inputType == 'radio') {
                        if (typeof this.value == 'array') {
                            return this.value;
                        } else {
                            return [this.value];
                        }
                    }

                    return (typeof this.value == 'string')
                        ? JSON.parse(this.value)
                        : this.value;
                },

                searchInTree(items, value, ancestors = []) {
                    for (let key in items) {
                        if (items[key][this.valueField] === value) {
                            return Object.assign(items[key], { ancestors: ancestors.reverse() });
                        }

                        const result = this.searchInTree(items[key][this.childrenField], value, [...ancestors, items[key]]);

                        if (result !== undefined) {
                            return result;
                        }
                    }

                    return undefined;
                },

                has(key) {
                    let foundValues = this.formattedValues.filter(value => value == key);
                    return foundValues.length > 0;
                },

                select(key) {
                    if (! this.has(key)) {
                        this.formattedValues.push(key);
                    }
                },

                unSelect(key) {
                    this.formattedValues = this.formattedValues.filter((savedKey) => savedKey !== key);
                },

                toggle(key) {
                    this.has(key) ? this.unSelect(key) : this.select(key);
                },

                handleCheckbox(key) {
                    let item = this.searchInTree(this.formattedItems, key);

                    switch (this.selectionType) {
                        case 'individual':
                            this.handleIndividualSelectionType(item);
                            break;

                        case 'hierarchical':
                            this.handleHierarchicalSelectionType(item);
                            break;

                        default:
                            this.handleHierarchicalSelectionType(item);
                            break;
                    }

                    this.$emit('change-input', this.formattedValues);
                },

                handleIndividualSelectionType(item) {
                    this.handleCurrent(item);
                },

                handleHierarchicalSelectionType(item) {
                    this.handleAncestors(item);
                    this.handleCurrent(item);
                    this.handleChildren(item);

                    if (! this.has(item[this.valueField])) {
                        this.unSelectAllChildren(item);
                    }
                },

                handleAncestors(item) {
                    if (item.ancestors && item.ancestors.length) {
                        item.ancestors.forEach((ancestor) => {
                            this.select(ancestor[this.valueField]);
                        });
                    }
                },

                handleCurrent(item) {
                    this.toggle(item[this.valueField]);
                },

                handleChildren(item) {
                    let selectedChildrenCount = this.countSelectedChildren(item);
                    selectedChildrenCount ? this.unSelectAllChildren(item) : this.selectAllChildren(item);
                },

                countSelectedChildren(item, selectedCount = 0) {
                    if (typeof item[this.childrenField] === 'object') {
                        for (let childKey in item[this.childrenField]) {
                            if (this.has(item[this.childrenField][childKey][this.valueField])) {
                                ++selectedCount;
                            }

                            this.countSelectedChildren(item[this.childrenField][childKey], selectedCount);
                        }
                    }

                    return selectedCount;
                },

                selectAllChildren(item) {
                    if (typeof item[this.childrenField] === 'object') {
                        for (let childKey in item[this.childrenField]) {
                            this.select(item[this.childrenField][childKey][this.valueField]);
                            this.selectAllChildren(item[this.childrenField][childKey]);
                        }
                    }
                },

                unSelectAllChildren(item) {
                    if (typeof item[this.childrenField] === 'object') {
                        for (let childKey in item[this.childrenField]) {
                            this.unSelect(item[this.childrenField][childKey][this.valueField]);
                            this.unSelectAllChildren(item[this.childrenField][childKey]);
                        }
                    }
                },
            },
        });

        // Tree Item Component
        app.component('tree-item', {
            name: 'tree-item',

            template: `
                <div>
                    <!-- Item Row -->
                    <div class="flex items-center py-1">
                        <component
                            :is="inputComponent"
                            :id="itemId"
                            :label="itemLabel"
                            :name="nameField"
                            :value="item[valueField]"
                            @change-input="onInputChange"
                        />
                    </div>

                    <!-- Children Items (Nested with connector lines) -->
                    <div v-if="hasChildren" class="tree-children">
                        <tree-item
                            v-for="(child, key) in item[childrenField]"
                            :key="key"
                            :item="child"
                            :level="level + 1"
                            :collapse="collapse"
                            :input-type="inputType"
                            :name-field="nameField"
                            :value-field="valueField"
                            :id-field="idField"
                            :label-field="labelField"
                            :children-field="childrenField"
                            :fallback-locale="fallbackLocale"
                            @toggle-item="$emit('toggle-item', $event)"
                        />
                    </div>
                </div>
            `,

            props: {
                item: Object,
                level: Number,
                collapse: Boolean,
                inputType: String,
                nameField: String,
                valueField: String,
                idField: String,
                labelField: String,
                childrenField: String,
                fallbackLocale: String,
            },

            computed: {
                hasChildren() {
                    return Object.entries(this.item[this.childrenField] || {}).length > 0;
                },

                inputComponent() {
                    return this.inputType === 'checkbox' ? 'v-tree-checkbox' : 'v-tree-radio';
                },

                itemId() {
                    const timestamp = new Date().getTime().toString(36);
                    const id = this.item[this.idField];
                    return `${timestamp}_${id}`;
                },

                itemLabel() {
                    return this.item[this.labelField]
                        ? this.item[this.labelField]
                        : this.item.translations.filter((translation) => translation.locale === this.fallbackLocale)[0][this.labelField];
                }
            },

            methods: {
                onInputChange(data) {
                    this.$emit('toggle-item', data.value);
                },

                // Expose these methods so v-tree-checkbox can access them via this.$parent
                has(key) {
                    let root = this.$parent;
                    while (root && root.$options.name !== 'v-tree-view') {
                        root = root.$parent;
                    }
                    return root ? root.has(key) : false;
                },

                select(key) {
                    let root = this.$parent;
                    while (root && root.$options.name !== 'v-tree-view') {
                        root = root.$parent;
                    }
                    if (root) root.select(key);
                },

                unSelect(key) {
                    let root = this.$parent;
                    while (root && root.$options.name !== 'v-tree-view') {
                        root = root.$parent;
                    }
                    if (root) root.unSelect(key);
                },
            },
        });
    </script>
@endPushOnce