<x-shop::form action="{{ route('shop.customers.account.quotes.'.(isset($action) ? $action.'_quote' : 'send_message'), $quote->id) }}">
    <x-shop::modal>
        <x-slot:toggle>
            <div class="{{ $buttonClass ?? 'primary-button' }} place-self-end rounded-lg px-11 py-3 max-md:max-w-full max-md:rounded-lg max-md:text-sm max-sm:w-full">
                @lang('b2b::app.shop.customers.account.quotes.view.'.$buttonText)
            </div>
        </x-slot>

        <x-slot:header>
            <h2 class="text-2xl font-medium max-md:text-base">
                @lang('b2b::app.shop.customers.account.quotes.view.'.(isset($action) ? $action.'-quote' : 'send-message'))
            </h2>
        </x-slot>

        <x-slot:content>
            @php
                // Initial draft submission: the buyer only edits the name + description they
                // filled at creation — the seller decides the dates.
                $isDraftSubmit = isset($action) && $action == 'submit' && $quote->status === 'draft';
            @endphp

            @if ($isDraftSubmit)
                <x-shop::form.control-group>
                    <x-shop::form.control-group.label>
                        @lang('b2b::app.shop.customers.account.quotes.view.quote-name')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="text"
                        name="name"
                        :value="old('name') ?? $quote->name"
                        rules="required"
                        :label="trans('b2b::app.shop.customers.account.quotes.view.quote-name')"
                    />

                    <x-shop::form.control-group.error control-name="name" />
                </x-shop::form.control-group>

                <x-shop::form.control-group class="!mb-0">
                    <x-shop::form.control-group.label>
                        @lang('b2b::app.shop.customers.account.quotes.view.quote-description')
                    </x-shop::form.control-group.label>

                    <x-shop::form.control-group.control
                        type="textarea"
                        name="description"
                        :value="old('description') ?? $quote->description"
                        :label="trans('b2b::app.shop.customers.account.quotes.view.quote-description')"
                    />

                    <x-shop::form.control-group.error control-name="description" />
                </x-shop::form.control-group>
            @else
                <!-- Items Fields (price negotiation) for a re-submit / counter-offer -->
                @if (isset($action) && $action == 'submit' && ($showItemFields ?? true))
                    @include('b2b::shop.customers.account.quotes.view.item-fields', ['quote' => $quote])
                @endif

                <x-shop::form.control-group class="!mb-0 mt-4">
                    <x-shop::form.control-group.control
                        type="textarea"
                        name="message"
                        class="px-6 py-4"
                        rules="required"
                        :placeholder="trans('b2b::app.shop.customers.account.quotes.view.message-placeholder')"
                    />

                    <x-shop::form.control-group.error
                        class="text-left"
                        control-name="message"
                    />
                </x-shop::form.control-group>

                @if (isset($action) && $action == 'delete')
                    <p class="mt-4 text-sm text-red-600">
                        @lang('b2b::app.shop.customers.account.quotes.view.delete-quote-msg')
                    </p>
                @endif
            @endif
        </x-slot>

        <!-- Modal Footer -->
        <x-slot:footer class="flex justify-end">
            <button
                type="submit"
                class="primary-button flex rounded-2xl px-11 py-3 max-md:rounded-lg max-md:px-6 max-md:text-sm"
            >
                @if (isset($actionButtonText))
                    @lang('b2b::app.shop.customers.account.quotes.view.'.$actionButtonText)
                @elseif (isset($action) && $action == 'submit')
                    @lang('b2b::app.shop.customers.account.quotes.view.btn-confirm-send')
                @else
                    @lang('b2b::app.shop.customers.account.quotes.view.btn-save')
                @endif
            </button>
        </x-slot>
    </x-shop::modal>
</x-shop::form>