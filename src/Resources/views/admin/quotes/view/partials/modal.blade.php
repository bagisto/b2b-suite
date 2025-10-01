<x-admin::form action="{{ route('admin.customers.quotes.'.(isset($action) ? $action.'_quote' : 'send_message'), $quote->id) }}">
    <x-admin::modal>
        <x-slot:toggle>
            <div class="{{ $buttonClass ?? 'primary-button' }} ">
                @lang('b2b_suite::app.admin.quotes.view.'.$buttonText)
            </div>
        </x-slot>

        <x-slot:header>
            <h2 class="text-base font-semibold text-gray-800 dark:text-white">
                @lang('b2b_suite::app.admin.quotes.view.send-message')
            </h2>
        </x-slot>

        <x-slot:content>
            <!-- Items Fields-->
            @if (isset($action) && $action == 'submit')
                @include('b2b_suite::admin.quotes.view.item-fields', ['quote' => $quote])
            @endif
            
            <x-admin::form.control-group class="!mb-0">
                <x-admin::form.control-group.control
                    type="textarea"
                    name="message"
                    class="px-6 py-4"
                    rules="required"
                    :placeholder="trans('b2b_suite::app.admin.quotes.view.message-placeholder')"
                />

                <x-admin::form.control-group.error
                    class="text-left"
                    control-name="message"
                />
            </x-admin::form.control-group>
        </x-slot>

        <!-- Modal Footer -->
        <x-slot:footer>
            <button
                type="submit"
                class="primary-button"
            >
                @lang('b2b_suite::app.admin.quotes.view.'.($actionButtonText ?? 'btn-save'))
            </button>
        </x-slot>
    </x-admin::modal>
</x-admin::form>