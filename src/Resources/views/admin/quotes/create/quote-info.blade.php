<!-- Quote Create Form -->
<x-admin::form
    :action="route('admin.customers.quotes.store', $cart->id)"
    enctype="multipart/form-data"
>
    <div class="box-shadow rounded bg-white dark:bg-gray-900">
        <div class="grid p-4 pt-2">
            <div class="flex justify-between p-4">
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    @lang('b2b_suite::app.admin.quotes.create.quote-title')
                </p>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('b2b_suite::app.admin.quotes.create.save-btn')
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-x-5">
                <div class="px-4">
                    <!-- Cart Id -->
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="cart_id"
                        :value="$cart->id"
                    />
                    
                    <!-- Quote Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                            @lang('b2b_suite::app.admin.quotes.create.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') ?? '' }}"
                            rules="required|min:3|max:255"
                            :label="trans('b2b_suite::app.admin.quotes.create.name')"
                            :placeholder="trans('b2b_suite::app.admin.quotes.create.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Quote Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                            @lang('b2b_suite::app.admin.quotes.create.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="description"
                            name="description"
                            value="{{ old('description') ?? '' }}"
                            rows="4"
                            rules="required|min:10|max:1000"
                            :label="trans('b2b_suite::app.admin.quotes.create.description')"
                            :placeholder="trans('b2b_suite::app.admin.quotes.create.description')"
                        />

                        <x-admin::form.control-group.error control-name="description" />
                    </x-admin::form.control-group>
                    
                    <!-- Status -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                            @lang('b2b_suite::app.admin.quotes.create.status')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="status"
                            id="status"
                            value="{{ old('status') ?? '' }}"
                            rules="required"
                            :label="trans('b2b_suite::app.admin.quotes.create.status')"
                        >
                            @foreach($statusLabels as $index => $label)
                                <option value="{{ $index }}"> 
                                    {{ $label }} 
                                </option>
                            @endforeach
                        </x-admin::form.control-group.control>
                        
                        <x-admin::form.control-group.error control-name="status" />
                    </x-admin::form.control-group>
                    
                    <!-- Order Date -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label>
                            @lang('b2b_suite::app.admin.quotes.create.order-date')
                        </x-admin::form.control-group.label>
                    
                        <x-admin::form.control-group.control
                            type="date"
                            id="order_date"
                            name="order_date"
                            value="{{ old('order_date') ?? '' }}"
                            data-min-date="today"
                            :label="trans('b2b_suite::app.admin.quotes.create.order-date')"
                        />

                        <x-admin::form.control-group.error control-name="order_date" />
                    </x-admin::form.control-group>
                    
                    <!-- Expected Arrival Date -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label>
                            @lang('b2b_suite::app.admin.quotes.create.expected-arrival')
                        </x-admin::form.control-group.label>
                    
                        <x-admin::form.control-group.control
                            type="date"
                            id="expected_arrival_date"
                            name="expected_arrival_date"
                            value="{{ old('expected_arrival_date') ?? '' }}"
                            data-min-date="today"
                            :label="trans('b2b_suite::app.admin.quotes.create.expected-arrival')"
                        />

                        <x-admin::form.control-group.error control-name="expected_arrival_date" />
                    </x-admin::form.control-group>
                    
                    <!-- Created At -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label>
                            @lang('b2b_suite::app.admin.quotes.create.created-at')
                        </x-admin::form.control-group.label>
                    
                        <x-admin::form.control-group.control
                            type="date"
                            id="created_at"
                            name="created_at"
                            value="{{ old('created_at') ?? '' }}"
                            :label="trans('b2b_suite::app.admin.quotes.create.created-at')"
                        />

                        <x-admin::form.control-group.error control-name="created_at" />
                    </x-admin::form.control-group>
                    
                    <!-- Expiration Date -->
                    <x-admin::form.control-group class="mt-4">
                        <x-admin::form.control-group.label class="required !text-gray-800 dark:!text-white">
                            @lang('b2b_suite::app.admin.quotes.create.expiration-date')
                        </x-admin::form.control-group.label>
                    
                        <x-admin::form.control-group.control
                            type="date"
                            id="expiration_date"
                            name="expiration_date"
                            value="{{ old('expiration_date') ?? '' }}"
                            rules="required"
                            data-min-date="today"
                            :label="trans('b2b_suite::app.admin.quotes.create.expiration-date')"
                        />

                        <x-admin::form.control-group.error control-name="expiration_date" />
                    </x-admin::form.control-group>
                </div>

                <div class="flex flex-1 flex-col px-4">
                    <!-- Quote Attachments -->
                    <div class="box-shadow relative bg-white p-4 dark:bg-gray-900">
                        <!-- Panel Header -->
                        <div class="mb-4 flex justify-between gap-5">
                            <div class="flex flex-col gap-2">
                                <p class="text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('b2b_suite::app.admin.quotes.create.attachments')
                                </p>

                                <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                                    @lang('b2b_suite::app.admin.quotes.create.attachment-info')
                                </p>
                            </div>
                        </div>

                        <!-- Attachment Blade Component -->
                        <x-admin::media.images
                            name="attachments"
                            allow-multiple="true"
                            show-placeholders="true"
                            :uploaded-images="[]"
                        />

                        <x-admin::form.control-group.error control-name='attachments[0]' />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin::form>