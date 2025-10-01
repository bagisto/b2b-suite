@php
    $supportedFormats = core()->getConfigData('b2b_suite.quotes.settings.supported_file_formats') ?? 'doc,docx,xls,xlsx,pdf,txt,jpg,png,jpeg';
    $maxFileSize = (int) (core()->getConfigData('b2b_suite.quotes.settings.maximum_file_size') ?: 2);
@endphp

<!-- Request Quote Modal Vue Component -->
<v-request-quote-modal 
    :cart="cart"
    @quote-submitted="$emit('quote-submitted')"
>
</v-request-quote-modal>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-request-quote-modal-template"
    >
        <div>
            {!! view_render_event('bagisto.shop.checkout.cart.request-quote.before') !!}

            <!-- Request Quote Form -->
            <x-shop::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, submitQuote)">
                    {!! view_render_event('bagisto.shop.checkout.cart.request-quote.form_controls.before') !!}

                    <!-- Request Quote Modal -->
                    <x-shop::modal ref="requestQuoteModal">
                        <!-- Modal Toggler -->
                        <x-slot:toggle>
                            <!-- This will be triggered by event -->
                        </x-slot>

                        <!-- Modal Header -->
                        <x-slot:header class="max-md:p-5">
                            <h2 class="text-2xl font-medium max-md:text-base">
                                @lang('b2b_suite::app.shop.checkout.cart.request-quote.title')
                            </h2>
                        </x-slot>

                        <!-- Modal Content -->
                        <x-slot:content class="!px-4 !py-6">
                            <div class="grid gap-6">
                                <!-- Quote Name Field -->
                                <x-shop::form.control-group class="!mb-0">
                                    <x-shop::form.control-group.label class="required">
                                        @lang('b2b_suite::app.shop.checkout.cart.quote-name')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="text"
                                        class="px-4 py-3 max-md:!p-3 max-sm:!p-2"
                                        name="name"
                                        rules="required|min:3|max:255"
                                        :placeholder="trans('b2b_suite::app.shop.checkout.cart.enter-quote-name')"
                                    />

                                    <x-shop::form.control-group.error
                                        class="flex"
                                        control-name="name"
                                    />
                                </x-shop::form.control-group>

                                <!-- Description Field -->
                                <x-shop::form.control-group class="!mb-0">
                                    <x-shop::form.control-group.label class="required">
                                        @lang('b2b_suite::app.shop.checkout.cart.add-your-description')
                                    </x-shop::form.control-group.label>

                                    <x-shop::form.control-group.control
                                        type="textarea"
                                        class="px-4 py-3 max-md:!p-3 max-sm:!p-2"
                                        name="description"
                                        rows="4"
                                        rules="required|min:10|max:1000"
                                        :placeholder="trans('b2b_suite::app.shop.checkout.cart.enter-brief-description')"
                                    />

                                    <x-shop::form.control-group.error
                                        class="flex"
                                        control-name="description"
                                    />
                                </x-shop::form.control-group>

                                <!-- File Attachment Field -->
                                <x-shop::form.control-group class="!mb-0">
                                    <x-shop::form.control-group.label>
                                        @lang('b2b_suite::app.shop.checkout.cart.attach-file')
                                    </x-shop::form.control-group.label>

                                    <!-- File Upload Area -->
                                    <div class="relative max-h-[100px] overflow-auto rounded-lg border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-gray-400">
                                        <input
                                            type="file"
                                            ref="fileInput"
                                            @change="handleFileSelect"
                                            class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                            :accept="acceptedFileTypes"
                                            multiple
                                        />

                                        <div v-if="selectedFiles.length === 0">
                                            <span class="icon-upload mb-3 block text-4xl text-gray-400"></span>
                                            <p class="mb-1 text-sm text-gray-600">
                                                @lang('b2b_suite::app.shop.checkout.cart.click-to-select-file')
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @lang('b2b_suite::app.shop.checkout.cart.file-requirements', [
                                                    'formats' => strtoupper($supportedFormats),
                                                    'size' => $maxFileSize . 'MB'
                                                ])
                                            </p>
                                        </div>

                                        <div v-else class="space-y-2">
                                            <div v-for="(file, idx) in selectedFiles" :key="file.name + file.size" class="mb-1.5 flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                                <div class="flex items-center">
                                                    <span class="icon-file mr-3 text-2xl text-blue-600"></span>
                                                    <div class="text-left">
                                                        <p class="text-sm font-medium text-gray-900">@{{ file.name }}</p>
                                                        <p class="text-xs text-gray-500">@{{ formatFileSize(file.size) }}</p>
                                                    </div>
                                                </div>
                                                
                                                <button
                                                    type="button"
                                                    @click="removeFile(idx)"
                                                    class="ml-4 text-red-500 hover:text-red-700"
                                                >
                                                    <span class="icon-cancel text-xl"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="fileError" class="mt-2 text-sm text-red-500">
                                        @{{ fileError }}
                                    </div>
                                </x-shop::form.control-group>

                                <!-- Cart Summary -->
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <h3 class="mb-3 text-lg font-semibold text-gray-900">
                                        @lang('shop::app.checkout.cart.summary.cart-summary')
                                    </h3>
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span>@lang('shop::app.checkout.cart.summary.sub-total')</span>
                                            <span>@{{ cart.formatted_sub_total }}</span>
                                        </div>
                                        <div v-if="cart.formatted_tax_total && parseFloat(cart.tax_total) > 0" class="flex justify-between text-sm">
                                            <span>@lang('shop::app.checkout.cart.summary.tax')</span>
                                            <span>@{{ cart.formatted_tax_total }}</span>
                                        </div>
                                        <div class="flex justify-between border-t pt-2 font-semibold">
                                            <span>@lang('shop::app.checkout.cart.summary.grand-total')</span>
                                            <span>@{{ cart.formatted_grand_total }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-slot>

                        <!-- Modal Footer -->
                        <x-slot:footer>
                            <div class="flex flex-wrap items-center gap-4 max-md:flex-col">
                                <!-- Action Buttons Container -->
                                <div class="flex w-full gap-3 max-md:flex-col">
                                    <!-- Save as Draft Button -->
                                    <x-shop::button
                                        type="button"
                                        class="secondary-button flex-1 rounded-2xl px-6 py-3 max-md:rounded-lg max-md:py-2"
                                        :title="trans('b2b_suite::app.shop.checkout.cart.request-quote.save-as-draft')"
                                        ::loading="isDraftSaving"
                                        ::disabled="isDraftSaving || isSubmitting"
                                        @click="saveAsDraft"
                                    />

                                    <!-- Request Quote Button -->
                                    <x-shop::button
                                        class="primary-button flex-1 rounded-2xl px-6 py-3 max-md:rounded-lg max-md:py-2"
                                        :title="trans('b2b_suite::app.shop.checkout.cart.request-quote.request-quote-submit')"
                                        ::loading="isSubmitting"
                                        ::disabled="isSubmitting || isDraftSaving"
                                    />
                                </div>
                            </div>
                        </x-slot>
                    </x-shop::modal>

                    {!! view_render_event('bagisto.shop.checkout.cart.request-quote.form_controls.after') !!}
                </form>
            </x-shop::form>

            {!! view_render_event('bagisto.shop.checkout.cart.request-quote.after') !!}
        </div>
    </script>

    <script type="module">
        app.component('v-request-quote-modal', {
            template: '#v-request-quote-modal-template',
            
            props: ['cart'],

            data() {
                return {
                    isSubmitting: false,
                    isDraftSaving: false,
                    selectedFiles: [],
                    fileError: null,
                    supportedFormats: '{{ $supportedFormats }}',
                    maxFileSize: {{ $maxFileSize }},
                };
            },

            computed: {
                acceptedFileTypes() {
                    return this.supportedFormats
                        .split(',')
                        .map(format => '.' + format.trim())
                        .join(',');
                }
            },

            mounted() {
                // Listen for modal open event
                this.$emitter.on('open-request-quote-modal', this.openModal);
            },

            methods: {
                openModal() {
                    this.$refs.requestQuoteModal.toggle();
                },

                closeModal() {
                    this.$refs.requestQuoteModal.toggle();
                    this.resetForm();
                },

                resetForm() {
                    this.selectedFiles = [];
                    this.fileError = null;
                    this.isSubmitting = false;
                    this.isDraftSaving = false;
                    // Clear file input
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                handleFileSelect(event) {
                    const files = Array.from(event.target.files);
                    this.fileError = null;
                    const allowedFormats = this.supportedFormats.toLowerCase().split(',').map(f => f.trim());
                    const maxSizeInBytes = this.maxFileSize * 1024 * 1024;
                    let validFiles = [];

                    for (const file of files) {
                        const fileExtension = file.name.split('.').pop().toLowerCase();
                        if (!allowedFormats.includes(fileExtension)) {
                            this.fileError = `@lang('b2b_suite::app.shop.checkout.cart.invalid-file-format', ['formats' => '${this.supportedFormats.toUpperCase()}'])`;
                            continue;
                        }
                        if (file.size > maxSizeInBytes) {
                            this.fileError = `@lang('b2b_suite::app.shop.checkout.cart.file-too-large', ['size' => '${this.maxFileSize}MB'])`;
                            continue;
                        }
                        validFiles.push(file);
                    }
                    this.selectedFiles = this.selectedFiles.concat(validFiles);
                    // Reset input so same file can be selected again if removed
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                removeFile(idx) {
                    this.selectedFiles.splice(idx, 1);
                    this.fileError = null;
                    if (this.selectedFiles.length === 0 && this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                submitQuote(params, { resetForm }) {
                    this.isSubmitting = true;
                    this.submitQuoteRequest(params, resetForm, 'open');
                },

                saveAsDraft() {
                    this.isDraftSaving = true;
                    
                    // Get form data
                    const formData = new FormData();
                    const quoteNameInput = document.querySelector('input[name="name"]');
                    const descriptionInput = document.querySelector('textarea[name="description"]');

                    if (!quoteNameInput?.value || !descriptionInput?.value) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: '@lang("b2b_suite::app.shop.checkout.cart.required-fields-draft")'
                        });
                        this.isDraftSaving = false;
                        return;
                    }

                    const params = {
                        name: quoteNameInput.value,
                        description: descriptionInput.value
                    };

                    this.submitQuoteRequest(params, () => {
                        quoteNameInput.value = '';
                        descriptionInput.value = '';
                    }, 'draft');
                },

                submitQuoteRequest(params, resetForm, status) {
                    const formData = new FormData();
                    formData.append('name', params.name);
                    formData.append('description', params.description);
                    formData.append('status', status);

                    // Append all selected files
                    if (this.selectedFiles.length > 0) {
                        this.selectedFiles.forEach((file, idx) => {
                            formData.append('attachments[]', file);
                        });
                    }

                    // Add cart data
                    if (this.cart) {
                        formData.append('cart_id', this.cart.id);
                    }

                    this.$axios.post('{{ route("b2b_suite.shop.quotes.store") }}', formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    })
                    .then((response) => {
                        this.isSubmitting = false;
                        this.isDraftSaving = false;

                        this.$emit('quote-submitted');

                        if (response.data.redirect_url) {
                            window.location.href = response.data.redirect_url;
                        }

                        this.$refs.requestQuoteModal.toggle();
                        resetForm();
                        this.resetForm();
                    })
                    .catch((error) => {
                        this.isSubmitting = false;
                        this.isDraftSaving = false;

                        if ([400, 422].includes(error.response?.status)) {
                            this.$emitter.emit('add-flash', { 
                                type: 'warning', 
                                message: error.response.data.message 
                            });
                            return;
                        }

                        this.$emitter.emit('add-flash', { 
                            type: 'error', 
                            message: error.response?.data?.message || '@lang("b2b_suite::app.shop.checkout.cart.request-failed")' 
                        });
                    });
                }
            }
        });
    </script>
@endPushOnce
