<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.customers.account.purchase-orders.index.title')
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
        <div class="mb-8 flex items-center max-sm:mb-5">
            <!-- Back Button -->
            <a
                class="grid md:hidden"
                href="{{ route('shop.customers.account.profile.index') }}"
            >
                <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
            </a>

            <h2 class="text-2xl font-medium max-sm:text-base ltr:ml-2.5 md:ltr:ml-0 rtl:mr-2.5 md:rtl:mr-0">
                @lang('b2b_suite::app.shop.customers.account.purchase-orders.index.title')
            </h2>
        </div>

        {!! view_render_event('bagisto.shop.customers.account.purchase_orders.list.before') !!}

        <!-- For Desktop View -->
        <div class="max-md:hidden">
            <x-shop::datagrid :src="route('shop.customers.account.purchase_orders.index')" />
        </div>

        <!-- For Mobile View -->
        <div class="md:hidden">
            <x-shop::datagrid :src="route('shop.customers.account.purchase_orders.index')">
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
                                            @lang('b2b_suite::app.shop.customers.account.quotes.order-id'): #@{{ record.id }}
    
                                            <p class="text-xs font-normal text-neutral-500">
                                                @{{ record.created_at }}
                                            </p>
                                        </div>
    
                                        <p v-html="record.status"></p>
                                    </div>
        
                                    <div class="mt-2.5 text-xs font-normal text-neutral-500">
                                        @lang('b2b_suite::app.shop.customers.account.quotes.subtotal')
    
                                        <p class="text-xl font-semibold text-black">
                                            @{{ record.grand_total }}
                                        </p>
                                    </div>
                                </a>
                            </div>
                        </template>
                    </template>
                </template>
            </x-shop::datagrid>
        </div>
    
        {!! view_render_event('bagisto.shop.customers.account.purchase_orders.list.after') !!}

    </div>
</x-shop::layouts.account>
