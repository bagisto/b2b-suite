<x-admin::layouts>
    <x-slot:title>
        @lang('b2b::app.admin.company-catalogs.index.title')
    </x-slot>

    @php
        // A catalog is editable only by its creator or a super-admin; everyone else gets a
        // view-only (eye) action that opens the read-only form.
        $currentAdminId = auth()->guard('admin')->user()->id;
        $isSuperAdmin   = optional(auth()->guard('admin')->user()->role)->permission_type === 'all';
    @endphp

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <!-- Title -->
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('b2b::app.admin.company-catalogs.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.b2b.company_catalogs.index') }}" />

            @if (bouncer()->hasPermission('b2b.company-catalogs.create'))
                <button
                    type="button"
                    class="primary-button"
                    @click="$emitter.emit('open-catalog-settings', null)"
                >
                    @lang('b2b::app.admin.company-catalogs.index.create-btn')
                </button>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.list.before') !!}

    <x-admin::datagrid :src="route('admin.b2b.company_catalogs.index')" :isMultiRow="true">
        <!-- Header -->
        <template #header="{ isLoading, available, applied, sort }">
            <template v-if="isLoading">
                <x-b2b::shimmer.datagrid.dg.head />
            </template>

            <template v-else>
                <div class="b2b-dg-head border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                        class="flex select-none items-center gap-2.5"
                        :class="{ 'b2b-dg-divider': index > 0 }"
                        v-for="(columnGroup, index) in [['name', 'status'], ['products_count', 'companies_count'], ['created_at'], []]"
                    >
                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(c => c.index === column)?.sortable,
                                        }"
                                        @click="available.columns.find(c => c.index === column)?.sortable ? sort(available.columns.find(c => c.index === column)) : {}"
                                    >
                                        @{{ available.columns.find(c => c.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 ltr:ml-1.5 rtl:mr-1.5 dark:text-white"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat' : 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            >
                            </i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <!-- Body -->
        <template #body="{ isLoading, available, performAction }">
            <template v-if="isLoading">
                <x-b2b::shimmer.datagrid.dg.body />
            </template>

            <template v-else>
                <div
                    class="b2b-dg-grid border-b px-4 py-4 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Identity -->
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">@{{ record.name }}</p>

                        <div v-html="record.status"></div>
                    </div>

                    <!-- Stats -->
                    <div class="b2b-dg-divider flex flex-col gap-3">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.company-catalogs.index.datagrid.products')
                            </span>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">@{{ record.products_count }}</p>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                @lang('b2b::app.admin.company-catalogs.index.datagrid.companies')
                            </span>
                            <p class="text-sm font-medium text-gray-800 dark:text-white">@{{ record.companies_count }}</p>
                        </div>
                    </div>

                    <!-- Created -->
                    <div class="b2b-dg-divider flex flex-col gap-0.5">
                        <span class="text-xs uppercase tracking-wide text-gray-400 dark:text-gray-500">
                            @lang('b2b::app.admin.company-catalogs.index.datagrid.created-at')
                        </span>
                        <p class="text-sm text-gray-600 dark:text-gray-300">@{{ record.created_at }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="b2b-dg-divider flex items-start justify-end gap-1.5">
                        <!-- Creator / super-admin: full controls (settings + edit + delete) -->
                        <template v-if="{{ $isSuperAdmin ? 'true' : 'false' }} || record.created_by == {{ $currentAdminId }}">
                            <!-- Update general settings (modal) -->
                            <span
                                class="icon-settings grid h-9 w-9 cursor-pointer place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                title="@lang('b2b::app.admin.company-catalogs.settings.edit-settings')"
                                @click="$emitter.emit('open-catalog-settings', record)"
                            ></span>

                            <template v-for="action in record.actions">
                                <span
                                    class="grid h-9 w-9 cursor-pointer place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                                    :class="action.icon"
                                    :title="action.title"
                                    @click="performAction(action)"
                                ></span>
                            </template>
                        </template>

                        <!-- Other admins: read-only view of the shared catalog -->
                        <a
                            v-else
                            :href="'{{ route('admin.b2b.company_catalogs.edit', 'ID_PLACEHOLDER') }}'.replace('ID_PLACEHOLDER', record.id)"
                            class="icon-view grid h-9 w-9 cursor-pointer place-items-center rounded-md border border-gray-200 text-gray-600 transition-all hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
                            title="@lang('b2b::app.admin.company-catalogs.index.datagrid.view')"
                        ></a>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    <!-- General settings modal (create + edit), opened via the emitter from the listing -->
    @if (bouncer()->hasPermission('b2b.company-catalogs.create') || bouncer()->hasPermission('b2b.company-catalogs.edit'))
        <v-catalog-settings
            create-url="{{ route('admin.b2b.company_catalogs.store') }}"
            settings-url-template="{{ route('admin.b2b.company_catalogs.settings', 'ID_PLACEHOLDER') }}"
        ></v-catalog-settings>
    @endif

    {!! view_render_event('bagisto.admin.b2b.company_catalogs.list.after') !!}

    @pushOnce('styles')
        <style>
            /**
             * Responsive layout for the company-catalog multi-row datagrid: one column on
             * mobile, four aligned columns on desktop. These grid utilities are purged out
             * of the B2B admin bundle, so they live in this scoped block.
             */
            .b2b-dg-grid,
            .b2b-dg-head {
                display: grid;
                grid-template-columns: minmax(0, 1fr);
            }

            .b2b-dg-grid {
                gap: 1rem;
            }

            .b2b-dg-head {
                display: none;
            }

            @media (min-width: 1024px) {
                .b2b-dg-grid,
                .b2b-dg-head {
                    grid-template-columns: minmax(0, 1.8fr) minmax(0, 1.2fr) minmax(0, 1fr) minmax(9.5rem, 0.9fr);
                    column-gap: 1.5rem;
                }

                .b2b-dg-grid {
                    row-gap: 0;
                    align-items: start;
                }

                .b2b-dg-head {
                    display: grid;
                    align-items: center;
                }

                .b2b-dg-divider {
                    border-inline-start: 1px solid rgb(243 244 246);
                    padding-inline-start: 1.5rem;
                }

                .dark .b2b-dg-divider {
                    border-inline-start-color: rgb(31 41 55);
                }
            }
        </style>
    @endPushOnce

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-catalog-settings-template"
        >
            <x-admin::modal ref="modal">
                <x-slot:header>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                        @{{ mode === 'edit'
                            ? "@lang('b2b::app.admin.company-catalogs.settings.edit-title')"
                            : "@lang('b2b::app.admin.company-catalogs.settings.create-title')" }}
                    </h2>
                </x-slot>

                <x-slot:content>
                    <form
                        ref="form"
                        :action="actionUrl"
                        method="post"
                        @submit.prevent="save"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="_method"
                            :value="mode === 'edit' ? 'PUT' : 'POST'"
                        >

                        <!-- Name -->
                        <div class="mb-4">
                            <label class="required mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                @lang('b2b::app.admin.company-catalogs.name')
                            </label>

                            <input
                                type="text"
                                name="name"
                                v-model="form.name"
                                class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                :class="{ '!border-red-600 hover:!border-red-600': errors.name }"
                                placeholder="@lang('b2b::app.admin.company-catalogs.settings.name-placeholder')"
                                @input="errors.name = ''"
                            >

                            <p
                                class="mt-1 text-xs text-red-600"
                                v-if="errors.name"
                            >
                                @{{ errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="mb-1.5 block text-sm font-medium text-gray-800 dark:text-white">
                                @lang('b2b::app.admin.company-catalogs.description')
                            </label>

                            <textarea
                                name="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-md border px-3 py-2.5 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                            ></textarea>

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('b2b::app.admin.company-catalogs.settings.description-info')
                            </p>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center gap-2">
                            <input
                                type="hidden"
                                name="status"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="status"
                                value="1"
                                id="catalog_settings_status"
                                class="peer hidden"
                                v-model="form.status"
                            >

                            <label
                                for="catalog_settings_status"
                                class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                            ></label>

                            <label
                                for="catalog_settings_status"
                                class="cursor-pointer text-sm font-medium text-gray-800 dark:text-white"
                            >
                                @lang('b2b::app.admin.company-catalogs.status')
                            </label>
                        </div>
                    </form>
                </x-slot>

                <x-slot:footer>
                    <button
                        type="button"
                        class="primary-button"
                        @click="save"
                    >
                        @{{ mode === 'edit'
                            ? "@lang('b2b::app.admin.company-catalogs.settings.save-btn')"
                            : "@lang('b2b::app.admin.company-catalogs.settings.next-btn')" }}
                    </button>
                </x-slot>
            </x-admin::modal>
        </script>

        <script type="module">
            app.component('v-catalog-settings', {
                template: '#v-catalog-settings-template',

                props: ['createUrl', 'settingsUrlTemplate'],

                data() {
                    return {
                        mode: 'create',
                        catalogId: null,
                        form: {
                            name: '',
                            description: '',
                            status: true,
                        },
                        errors: {
                            name: '',
                        },
                    };
                },

                computed: {
                    /**
                     * Resolve the form action: the store route when creating, or the
                     * settings route (with the catalog id substituted) when editing.
                     */
                    actionUrl() {
                        if (this.mode === 'edit') {
                            return this.settingsUrlTemplate.replace('ID_PLACEHOLDER', this.catalogId);
                        }

                        return this.createUrl;
                    },
                },

                created() {
                    /**
                     * Open the modal from anywhere on the listing: a null payload starts a
                     * create, a datagrid record prefills the general settings for editing.
                     */
                    this.$emitter.on('open-catalog-settings', (record) => {
                        record ? this.openEdit(record) : this.openCreate();
                    });
                },

                methods: {
                    /**
                     * Reset the form to a blank, active catalog and open the modal.
                     */
                    openCreate() {
                        this.mode = 'create';
                        this.catalogId = null;
                        this.form = { name: '', description: '', status: true };
                        this.errors.name = '';

                        this.$refs.modal.open();
                    },

                    /**
                     * Prefill the form from a datagrid record and open the modal for editing.
                     */
                    openEdit(record) {
                        this.mode = 'edit';
                        this.catalogId = record.id;
                        this.form = {
                            name: record.name || '',
                            description: record.description || '',
                            status: Number(record.status_value) === 1,
                        };
                        this.errors.name = '';

                        this.$refs.modal.open();
                    },

                    /**
                     * Validate the name, then submit the underlying form (a native submit so
                     * the page navigates: create continues to the edit screen, edit returns
                     * to the listing).
                     */
                    save() {
                        if (! this.form.name.trim()) {
                            this.errors.name = "@lang('b2b::app.admin.company-catalogs.settings.name-required')";

                            return;
                        }

                        this.$refs.form.submit();
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
