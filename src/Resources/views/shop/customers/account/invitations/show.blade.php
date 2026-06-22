<x-shop::layouts.account>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.customers.account.invitations.title')
    </x-slot>

    <div class="max-md:hidden">
        <x-shop::layouts.account.navigation />
    </div>

    <div class="mx-4 flex-auto max-md:mx-6 max-sm:mx-4">
        <div class="mb-8 flex items-center max-md:mb-5">
            <h2 class="text-2xl font-medium max-md:text-xl max-sm:text-base">
                @lang('b2b::app.shop.customers.account.invitations.title')
            </h2>
        </div>

        <div class="max-w-xl rounded-xl border border-zinc-200 p-6">
            <p class="text-base text-gray-800">
                {!! trans('b2b::app.shop.customers.account.invitations.intro', [
                    'company' => '<strong>'.e($company?->businessName() ?: $company?->name).'</strong>',
                    'role'    => '<strong>'.e($role?->name ?: trans('b2b::app.shop.customers.account.invitations.member')).'</strong>',
                ]) !!}
            </p>

            <p class="mt-3 text-sm text-zinc-500">
                @lang('b2b::app.shop.customers.account.invitations.note')
            </p>

            <div class="mt-6 flex items-center gap-3 max-sm:flex-col max-sm:items-stretch">
                <!-- Accept -->
                <x-shop::form
                    :action="route('shop.customers.account.invitations.accept', $invitation->token)"
                    class="m-0"
                >
                    <button
                        type="submit"
                        class="primary-button rounded-2xl px-11 py-3 text-base max-md:rounded-lg max-md:py-2 max-sm:w-full"
                    >
                        @lang('b2b::app.shop.customers.account.invitations.btn-accept')
                    </button>
                </x-shop::form>

                <!-- Decline -->
                <x-shop::form
                    :action="route('shop.customers.account.invitations.decline', $invitation->token)"
                    class="m-0"
                >
                    <button
                        type="submit"
                        class="secondary-button rounded-2xl border-zinc-200 px-11 py-3 text-base max-md:rounded-lg max-md:py-2 max-sm:w-full"
                    >
                        @lang('b2b::app.shop.customers.account.invitations.btn-decline')
                    </button>
                </x-shop::form>
            </div>
        </div>
    </div>
</x-shop::layouts.account>
