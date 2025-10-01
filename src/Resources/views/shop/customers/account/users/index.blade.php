<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.users.index.title')
    </x-slot>

    <!-- Breadcrumbs -->
    @if ((core()->getConfigData('general.general.breadcrumbs.shop')))
        @section('breadcrumbs')
            <x-shop::breadcrumbs name="orders" />
        @endSection
    @endif

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Back Button -->
                <a
                    class="grid md:hidden"
                    href="{{ route('shop.customers.account.profile.index') }}"
                >
                    <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                </a>

                <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                    @lang('b2b_suite::app.shop.customers.account.users.index.title')
                </h2>
            </div>

            <a
                href="{{ route('shop.customers.account.users.create') }}"
                class="secondary-button border-zinc-200 px-5 py-3 font-normal max-md:rounded-lg max-md:py-2 max-sm:py-1.5 max-sm:text-sm"
            >
                @lang('b2b_suite::app.shop.customers.account.users.index.add-btn') 
            </a>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.users.list.before') !!}

        <!-- For Desktop View -->
        <div class="max-md:hidden">
            <x-shop::datagrid :src="route('shop.customers.account.users.index')" />
        </div>

        <!-- For Mobile View -->
        <div class="md:hidden">
            <x-shop::datagrid :src="route('shop.customers.account.users.index')">
                <!-- Datagrid Header -->
                <template #header="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <div class="hidden"></div>
                </template>

                <template #body="{
                    isLoading,
                    available,
                    applied,
                    selectAll,
                    sort,
                    performAction
                }">
                    <template v-if="isLoading">
                        <x-shop::shimmer.datagrid.table.body />
                    </template>
    
                    <template v-else>
                        <template v-for="record in available.records">
                            <div class="w-full p-4 border rounded-md transition-all hover:bg-gray-50 [&>*]:border-0 mb-4 last:mb-0">
                                <a :href="record.actions[0].url">
                                    <div class="flex justify-between">
                                        <div class="text-sm font-semibold">
                                            @lang('b2b_suite::app.shop.customers.account.users.index.datagrid.id'): #@{{ record.user_id }}
                                        </div>
    
                                        <p v-html="record.status"></p>
                                    </div>
            
                                    <div class="grid gap-2 text-xs font-normal">
                                        <p
                                            class="text-sm font-semibold text-blue-600"
                                            v-html="record.full_name"
                                        >
                                        </p>

                                        <p>
                                            <span class="text-neutral-500">@lang('b2b_suite::app.shop.customers.account.users.index.datagrid.email'): </span>
                                            <span class="font-medium">@{{ record.email }}</span>
                                        </p>

                                        <p>
                                            <span class="text-neutral-500">@lang('b2b_suite::app.shop.customers.account.users.index.datagrid.phone'): </span>
                                            <span class="font-medium">@{{ record.phone }}</span>
                                        </p>

                                        <p>
                                            <span class="text-neutral-500">@lang('b2b_suite::app.shop.customers.account.users.index.datagrid.group'): </span>
                                            <span class="font-medium">@{{ record.group }}</span>
                                        </p>
                                    </div>
        
                                    <div class="mt-4 flex justify-between">
                                        <div class="text-xs font-normal text-neutral-500">@lang('b2b_suite::app.shop.customers.account.users.index.datagrid.is-suspended'):</div>
                                        <p v-html="record.is_suspended"></p>
                                    </div>
                                </a>
                            </div>
                        </template>
                    </template>
                </template>
            </x-shop::datagrid>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.users.list.after') !!}

    </div>
</x-shop::layouts.account>
