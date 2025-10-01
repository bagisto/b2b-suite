<x-shop::form action="{{ route('shop.customers.account.quotes.'.(isset($action) ? $action.'_quote' : 'send_message'), $quote->id) }}">
    <x-shop::modal>
        <x-slot:toggle>
            <div class="{{ $buttonClass ?? 'primary-button' }} place-self-end rounded-lg px-11 py-3 max-md:max-w-full max-md:rounded-lg max-md:text-sm max-sm:w-full">
                @lang('b2b_suite::app.shop.customers.account.quotes.view.'.$buttonText)
            </div>
        </x-slot>

        <x-slot:header>
            <h2 class="text-2xl font-medium max-md:text-base">
                @lang('b2b_suite::app.shop.customers.account.quotes.view.'.(isset($action) ? $action.'-quote' : 'send-message'))
            </h2>
        </x-slot>

        <x-slot:content>
            <!-- Items Fields-->
            @if (isset($action) && $action == 'submit')
                @include('b2b_suite::shop.customers.account.quotes.view.item-fields', ['quote' => $quote])
            @endif
            
            <x-shop::form.control-group class="!mb-0 mt-4">
                <x-shop::form.control-group.control
                    type="textarea"
                    name="message"
                    class="px-6 py-4"
                    rules="required"
                    :placeholder="trans('b2b_suite::app.shop.customers.account.quotes.view.message-placeholder')"
                />

                <x-shop::form.control-group.error
                    class="text-left"
                    control-name="message"
                />
            </x-shop::form.control-group>

            @if (isset($action) && $action == 'delete')
                <p class="mt-4 text-sm text-red-600">
                    @lang('b2b_suite::app.shop.customers.account.quotes.view.delete-quote-msg')
                </p>
            @endif
        </x-slot>

        <!-- Modal Footer -->
        <x-slot:footer>
            <button
                type="submit"
                class="primary-button flex rounded-2xl px-11 py-3 max-md:rounded-lg max-md:px-6 max-md:text-sm"
            >
                @lang('b2b_suite::app.shop.customers.account.quotes.view.'.($actionButtonText ?? 'btn-save'))
            </button>
        </x-slot>
    </x-shop::modal>
</x-shop::form>