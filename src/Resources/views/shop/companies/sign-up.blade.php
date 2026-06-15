<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="@lang('b2b::app.shop.companies.signup-form.page-title')"
    />

    <meta
        name="keywords"
        content="@lang('b2b::app.shop.companies.signup-form.page-title')"
    />
@endPush

@php
    /**
     * Decide which account type tab should be open on load. Priority:
     *   1. The `account_type` from the last (failed) submission so errors stay in context.
     *   2. The `?type=company` query param used by the sign-in page links.
     *   3. Default to the personal account tab.
     */
    $activeTab = old('account_type', request('type') === 'company' ? 'company' : 'personal');
@endphp

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b::app.shop.companies.signup-form.page-title')
    </x-slot>

    <div class="container mt-20 max-1180:px-5 max-md:mt-12">
        {!! view_render_event('bagisto.shop.customers.sign-up.logo.before') !!}

        <!-- Company Logo -->
        <div class="flex items-center gap-x-14 max-[1180px]:gap-x-9">
            <a
                href="{{ route('shop.home.index') }}"
                class="m-[0_auto_20px_auto]"
                aria-label="@lang('b2b::app.shop.companies.signup-form.bagisto')"
            >
                <img
                    src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                    width="131"
                    height="29"
                >
            </a>
        </div>

        {!! view_render_event('bagisto.shop.customers.sign-up.logo.after') !!}

        <!-- Form Container -->
        <div class="m-auto w-full max-w-[870px] rounded-2xl border border-zinc-200 bg-white p-16 shadow-sm max-md:p-8 max-sm:border-none max-sm:p-0">
            <h1 class="font-dmserif text-4xl text-navyBlue max-md:text-3xl max-sm:text-2xl">
                @lang('shop::app.customers.signup-form.page-title')
            </h1>

            <p class="mt-3 text-lg text-zinc-500 max-sm:text-sm">
                @lang('b2b::app.shop.companies.signup-form.choose-account-type')
            </p>

            <!-- Account Type Selector -->
            <div class="mt-8 grid grid-cols-2 gap-5 max-sm:grid-cols-1">
                <button
                    type="button"
                    id="tab-personal"
                    onclick="b2bSwitchTab('personal')"
                    class="b2b-account-card group flex items-start gap-4 rounded-xl border border-zinc-200 p-5 text-left transition-all hover:border-navyBlue {{ $activeTab === 'personal' ? 'b2b-tab-active' : '' }}"
                >
                    <span class="b2b-icon-badge flex items-center justify-center rounded-full bg-lightOrange text-navyBlue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </span>

                    <span class="flex flex-col">
                        <span class="font-semibold text-navyBlue">
                            @lang('b2b::app.shop.companies.signup-form.tab-personal')
                        </span>

                        <span class="mt-1 text-sm text-zinc-500">
                            @lang('b2b::app.shop.companies.signup-form.personal-text')
                        </span>
                    </span>
                </button>

                <button
                    type="button"
                    id="tab-company"
                    onclick="b2bSwitchTab('company')"
                    class="b2b-account-card group flex items-start gap-4 rounded-xl border border-zinc-200 p-5 text-left transition-all hover:border-navyBlue {{ $activeTab === 'company' ? 'b2b-tab-active' : '' }}"
                >
                    <span class="b2b-icon-badge flex items-center justify-center rounded-full bg-lightOrange text-navyBlue">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 21h18"/>
                            <path d="M5 21V7l8-4v18"/>
                            <path d="M19 21V11l-6-4"/>
                            <path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01"/>
                        </svg>
                    </span>

                    <span class="flex flex-col">
                        <span class="font-semibold text-navyBlue">
                            @lang('b2b::app.shop.companies.signup-form.tab-company')
                        </span>

                        <span class="mt-1 text-sm text-zinc-500">
                            @lang('b2b::app.shop.companies.signup-form.company-text')
                        </span>
                    </span>
                </button>
            </div>

            {{--
                ============================================================
                Personal Account form — posts to the core customer register.
                ============================================================
            --}}
            <div
                id="panel-personal"
                class="mt-10 {{ $activeTab === 'personal' ? '' : 'hidden' }}"
            >
                <h2 class="font-dmserif text-2xl text-navyBlue max-sm:text-xl">
                    @lang('b2b::app.shop.companies.signup-form.personal-title')
                </h2>

                <div class="mt-6 rounded">
                    <x-shop::form :action="route('shop.customers.register.store')">
                        <input type="hidden" name="account_type" value="personal">

                        {!! view_render_event('bagisto.shop.customers.signup_form_controls.before') !!}

                        <!-- First Name -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.customers.signup-form.first-name')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="text"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="first_name"
                                rules="required"
                                :value="old('first_name')"
                                :label="trans('shop::app.customers.signup-form.first-name')"
                                :placeholder="trans('shop::app.customers.signup-form.first-name')"
                                :aria-label="trans('shop::app.customers.signup-form.first-name')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="first_name" />
                        </x-shop::form.control-group>

                        <!-- Last Name -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.customers.signup-form.last-name')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="text"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="last_name"
                                rules="required"
                                :value="old('last_name')"
                                :label="trans('shop::app.customers.signup-form.last-name')"
                                :placeholder="trans('shop::app.customers.signup-form.last-name')"
                                :aria-label="trans('shop::app.customers.signup-form.last-name')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="last_name" />
                        </x-shop::form.control-group>

                        <!-- Email -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.customers.signup-form.email')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="email"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="email"
                                rules="required|email"
                                :value="old('email')"
                                :label="trans('shop::app.customers.signup-form.email')"
                                placeholder="email@example.com"
                                :aria-label="trans('shop::app.customers.signup-form.email')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="email" />
                        </x-shop::form.control-group>

                        <!-- Password -->
                        <x-shop::form.control-group class="mb-6">
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.customers.signup-form.password')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="password"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="password"
                                rules="required|min:6"
                                :value="old('password')"
                                :label="trans('shop::app.customers.signup-form.password')"
                                :placeholder="trans('shop::app.customers.signup-form.password')"
                                ref="password"
                                :aria-label="trans('shop::app.customers.signup-form.password')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="password" />
                        </x-shop::form.control-group>

                        <!-- Confirm Password -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label>
                                @lang('shop::app.customers.signup-form.confirm-pass')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="password"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="password_confirmation"
                                rules="confirmed:@password"
                                value=""
                                :label="trans('shop::app.customers.signup-form.password')"
                                :placeholder="trans('shop::app.customers.signup-form.confirm-pass')"
                                :aria-label="trans('shop::app.customers.signup-form.confirm-pass')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="password_confirmation" />
                        </x-shop::form.control-group>

                        <!-- Captcha -->
                        @if (core()->getConfigData('customer.captcha.credentials.status'))
                            <x-shop::form.control-group>
                                {!! \Webkul\Customer\Facades\Captcha::render() !!}

                                <x-shop::form.control-group.error control-name="recaptcha_token" />
                            </x-shop::form.control-group>
                        @endif

                        <!-- Subscribed Button -->
                        @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                            <div class="mb-5 flex select-none items-center gap-1.5">
                                <input
                                    type="checkbox"
                                    name="is_subscribed"
                                    id="is-subscribed-personal"
                                    class="peer hidden"
                                />

                                <label
                                    class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                    for="is-subscribed-personal"
                                ></label>

                                <label
                                    class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm ltr:pl-0 rtl:pr-0"
                                    for="is-subscribed-personal"
                                >
                                    @lang('shop::app.customers.signup-form.subscribe-to-newsletter')
                                </label>
                            </div>
                        @endif

                        @if(
                            core()->getConfigData('general.gdpr.settings.enabled')
                            && core()->getConfigData('general.gdpr.agreement.enabled')
                        )
                            <div class="mb-2 flex select-none items-center gap-1.5">
                                <x-shop::form.control-group.control
                                    type="checkbox"
                                    name="agreement"
                                    id="agreement-personal"
                                    value="0"
                                    rules="required"
                                    for="agreement-personal"
                                />

                                <label
                                    class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm"
                                    for="agreement-personal"
                                    v-pre
                                >
                                    {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                                </label>

                                @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                                    <span
                                        class="cursor-pointer text-base text-navyBlue max-sm:text-sm"
                                        @click="$refs.termsModal.open()"
                                    >
                                        @lang('shop::app.customers.signup-form.click-here')
                                    </span>
                                @endif
                            </div>

                            <x-shop::form.control-group.error control-name="agreement" />
                        @endif

                        <div class="mt-8">
                            <button
                                class="primary-button m-0 block w-full max-w-[374px] rounded-2xl px-11 py-4 text-center text-base max-md:max-w-full max-md:rounded-lg max-md:py-3 max-sm:py-2.5 ltr:ml-0 rtl:mr-0"
                                type="submit"
                            >
                                @lang('shop::app.customers.signup-form.button-title')
                            </button>
                        </div>

                        {!! view_render_event('bagisto.shop.customers.signup_form_controls.after') !!}
                    </x-shop::form>
                </div>
            </div>

            {{--
                ============================================================
                Company Account form — posts to the B2B company register.
                Renders dynamic company sign-up attributes.
                ============================================================
            --}}
            <div
                id="panel-company"
                class="mt-10 {{ $activeTab === 'company' ? '' : 'hidden' }}"
            >
                <h2 class="font-dmserif text-2xl text-navyBlue max-sm:text-xl">
                    @lang('b2b::app.shop.companies.signup-form.company-title')
                </h2>

                <div class="mt-6 rounded">
                    <x-shop::form
                        :action="route('shop.companies.register.store')"
                        enctype="multipart/form-data"
                    >
                        <input type="hidden" name="account_type" value="company">

                        @foreach ($attributes as $attribute)
                            <x-shop::form.control-group>
                                <x-shop::form.control-group.label :class="$attribute->is_required ? 'required' : ''">
                                    {{ $attribute->name }}
                                </x-shop::form.control-group.label>

                                @include('b2b::shop.companies.sign-up.controls', [
                                    'attribute' => $attribute,
                                ])

                                <x-shop::form.control-group.error :control-name="$attribute->code" />
                            </x-shop::form.control-group>

                            {!! view_render_event("bagisto.shop.customers.signup_form.{$attribute->code}.after", ['attribute' => $attribute]) !!}
                        @endforeach

                        <!-- Password -->
                        <x-shop::form.control-group class="mb-6">
                            <x-shop::form.control-group.label class="required">
                                @lang('b2b::app.shop.companies.signup-form.password')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="password"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="password"
                                rules="required|min:6"
                                :value="old('password')"
                                :label="trans('b2b::app.shop.companies.signup-form.password')"
                                :placeholder="trans('b2b::app.shop.companies.signup-form.password')"
                                ref="companyPassword"
                                :aria-label="trans('b2b::app.shop.companies.signup-form.password')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="password" />
                        </x-shop::form.control-group>

                        <!-- Confirm Password -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label>
                                @lang('b2b::app.shop.companies.signup-form.confirm-pass')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="password"
                                class="px-6 py-4 max-md:py-3 max-sm:py-2"
                                name="password_confirmation"
                                rules="confirmed:@password"
                                value=""
                                :label="trans('b2b::app.shop.companies.signup-form.password')"
                                :placeholder="trans('b2b::app.shop.companies.signup-form.confirm-pass')"
                                :aria-label="trans('b2b::app.shop.companies.signup-form.confirm-pass')"
                                aria-required="true"
                            />

                            <x-shop::form.control-group.error control-name="password_confirmation" />
                        </x-shop::form.control-group>

                        @if (core()->getConfigData('customer.captcha.credentials.status'))
                            <div class="mb-5 flex">
                                {!! \Webkul\Customer\Facades\Captcha::render() !!}
                            </div>
                        @endif

                        <!-- Subscribed Button -->
                        @if (core()->getConfigData('customer.settings.create_new_account_options.news_letter'))
                            <div class="mb-5 flex select-none items-center gap-1.5">
                                <input
                                    type="checkbox"
                                    name="is_subscribed"
                                    id="is-subscribed-company"
                                    class="peer hidden"
                                />

                                <label
                                    class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                    for="is-subscribed-company"
                                ></label>

                                <label
                                    class="cursor-pointer select-none text-base text-zinc-500 ltr:pl-0 rtl:pr-0 max-sm:text-sm"
                                    for="is-subscribed-company"
                                >
                                    @lang('b2b::app.shop.companies.signup-form.subscribe-to-newsletter')
                                </label>
                            </div>
                        @endif

                        @if(
                            core()->getConfigData('general.gdpr.settings.enabled')
                            && core()->getConfigData('general.gdpr.agreement.enabled')
                        )
                            <div class="mb-2 flex select-none items-center gap-1.5">
                                <x-shop::form.control-group.control
                                    type="checkbox"
                                    name="agreement"
                                    id="agreement-company"
                                    value="0"
                                    rules="required"
                                    for="agreement-company"
                                />

                                <label
                                    class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm"
                                    for="agreement-company"
                                >
                                    {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                                </label>

                                @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                                    <span
                                        class="cursor-pointer text-base text-navyBlue max-sm:text-sm"
                                        @click="$refs.termsModal.open()"
                                    >
                                        @lang('b2b::app.shop.companies.signup-form.click-here')
                                    </span>
                                @endif
                            </div>

                            <x-shop::form.control-group.error control-name="agreement" />
                        @endif

                        <div class="mt-8">
                            <button
                                class="primary-button m-0 block w-full max-w-[374px] rounded-2xl px-11 py-4 text-center text-base ltr:ml-0 rtl:mr-0 max-md:max-w-full max-md:rounded-lg max-md:py-3 max-sm:py-2.5"
                                type="submit"
                            >
                                @lang('b2b::app.shop.companies.signup-form.button-title')
                            </button>
                        </div>
                    </x-shop::form>
                </div>
            </div>

            <p class="mt-8 border-t border-zinc-100 pt-6 font-medium text-zinc-500 max-sm:text-center max-sm:text-sm">
                @lang('b2b::app.shop.companies.signup-form.account-exists')

                <a
                    class="font-semibold text-navyBlue hover:underline"
                    href="{{ route('shop.customer.session.index') }}"
                >
                    @lang('b2b::app.shop.companies.signup-form.sign-in-button')
                </a>
            </p>
        </div>

        <p class="mb-4 mt-8 text-center text-xs text-zinc-500">
            @lang('b2b::app.shop.companies.signup-form.footer', ['current_year'=> date('Y') ])
        </p>
    </div>

    @push('styles')
        <style>
            .b2b-account-card.b2b-tab-active {
                border-color: #060C3B;
                background-color: #F6F2EB;
                box-shadow: inset 0 0 0 1px #060C3B;
            }

            /* Account-type icon badge (classes not present in the core shop stylesheet). */
            .b2b-icon-badge {
                height: 2.75rem;
                width: 2.75rem;
                flex-shrink: 0;
            }
        </style>
    @endpush

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}

        <script>
            /**
             * Toggle between the personal and company account registration panels.
             *
             * @param {string} type - Either "personal" or "company".
             */
            function b2bSwitchTab(type) {
                ['personal', 'company'].forEach(function (current) {
                    document
                        .getElementById('panel-' + current)
                        .classList.toggle('hidden', current !== type);

                    document
                        .getElementById('tab-' + current)
                        .classList.toggle('b2b-tab-active', current === type);
                });
            }
        </script>
    @endpush

    <!-- Terms & Conditions Modal -->
    <x-shop::modal ref="termsModal">
        <x-slot:toggle></x-slot>

        <x-slot:header class="!p-5">
            <p>@lang('b2b::app.shop.companies.signup-form.terms-conditions')</p>
        </x-slot>

        <x-slot:content class="!p-5">
            <div class="max-h-[500px] overflow-auto">
                {!! core()->getConfigData('general.gdpr.agreement.agreement_content') !!}
            </div>
        </x-slot>
    </x-shop::modal>
</x-shop::layouts>
