<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="@lang('b2b_suite::app.shop.companies.signup-form.page-title')"
    />

    <meta
        name="keywords"
        content="@lang('b2b_suite::app.shop.companies.signup-form.page-title')"
    />
@endPush

<x-shop::layouts
    :has-header="false"
    :has-feature="false"
    :has-footer="false"
>
    <!-- Page Title -->
    <x-slot:title>
        @lang('b2b_suite::app.shop.companies.signup-form.page-title')
    </x-slot>

	<div class="container mt-20 max-1180:px-5 max-md:mt-12">
        {!! view_render_event('bagisto.shop.customers.sign-up.logo.before') !!}

        <!-- Company Logo -->
        <div class="flex items-center gap-x-14 max-[1180px]:gap-x-9">
            <a
                href="{{ route('shop.home.index') }}"
                class="m-[0_auto_20px_auto]"
                aria-label="@lang('b2b_suite::app.shop.companies.signup-form.bagisto')"
            >
                <img
                    src="{{ core()->getCurrentChannel()->logo_url ?? bagisto_asset('images/logo.svg') }}"
                    alt="{{ config('app.name') }}"
                    width="131"
                    height="29"
                >
            </a>
        </div>

        {!! view_render_event('bagisto.shop.customers.sign-up.logo.before') !!}

        <!-- Form Container -->
		<div class="m-auto w-full max-w-[870px] rounded-xl border border-zinc-200 p-16 px-[90px] max-md:px-8 max-md:py-8 max-sm:border-none max-sm:p-0">
			<h1 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl">
                @lang('b2b_suite::app.shop.companies.signup-form.page-title')
            </h1>

			<p class="mt-4 text-xl text-zinc-500 max-sm:mt-0 max-sm:text-sm">
                @lang('b2b_suite::app.shop.companies.signup-form.form-signup-text')
            </p>

            <div class="mt-14 rounded max-sm:mt-8">
                <x-shop::form
                    :action="route('shop.companies.register.store')"
                    enctype="multipart/form-data"
                >
                    {!! view_render_event('bagisto.shop.customers.signup_form_controls.before') !!}

                    @foreach ($attributes as $attribute)
                        <x-shop::form.control-group class="mb-6">
                            <x-shop::form.control-group.label>
                                {!! $attribute->name . ($attribute->is_required ? '<span class="required"></span>' : '') !!}
                            </x-shop::form.control-group.label>

                            @include('b2b_suite::shop.companies.sign-up.controls', [
                                'attribute' => $attribute,
                            ])
                                
                            <x-shop::form.control-group.error :control-name="$attribute->code" />
                        </x-shop::form.control-group>

                        {!! view_render_event("bagisto.shop.customers.signup_form.{$attribute->code}.after", ['attribute' => $attribute]) !!}
                    @endforeach

                    <!-- Password -->
                    <x-shop::form.control-group class="mb-6">
                        <x-shop::form.control-group.label class="required">
                            @lang('b2b_suite::app.shop.companies.signup-form.password')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="password"
                            class="px-6 py-4 max-md:py-3 max-sm:py-2"
                            name="password"
                            rules="required|min:6"
                            :value="old('password')"
                            :label="trans('b2b_suite::app.shop.companies.signup-form.password')"
                            :placeholder="trans('b2b_suite::app.shop.companies.signup-form.password')"
                            ref="password"
                            :aria-label="trans('b2b_suite::app.shop.companies.signup-form.password')"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="password" />
                    </x-shop::form.control-group>

                    {!! view_render_event('bagisto.shop.customers.signup_form.password.after') !!}

                    <!-- Confirm Password -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label>
                            @lang('b2b_suite::app.shop.companies.signup-form.confirm-pass')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="password"
                            class="px-6 py-4 max-md:py-3 max-sm:py-2"
                            name="password_confirmation"
                            rules="confirmed:@password"
                            value=""
                            :label="trans('b2b_suite::app.shop.companies.signup-form.password')"
                            :placeholder="trans('b2b_suite::app.shop.companies.signup-form.confirm-pass')"
                            :aria-label="trans('b2b_suite::app.shop.companies.signup-form.confirm-pass')"
                            aria-required="true"
                        />

                        <x-shop::form.control-group.error control-name="password_confirmation" />
                    </x-shop::form.control-group>

                    {!! view_render_event('bagisto.shop.customers.signup_form.password_confirmation.after') !!}

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
                                id="is-subscribed"
                                class="peer hidden"
                            />

                            <label
                                class="icon-uncheck peer-checked:icon-check-box cursor-pointer text-2xl text-navyBlue peer-checked:text-navyBlue"
                                for="is-subscribed"
                            ></label>

                            <label
                                class="cursor-pointer select-none text-base text-zinc-500 ltr:pl-0 rtl:pr-0 max-sm:text-sm"
                                for="is-subscribed"
                            >
                                @lang('b2b_suite::app.shop.companies.signup-form.subscribe-to-newsletter')
                            </label>
                        </div>
                    @endif

                    {!! view_render_event('bagisto.shop.customers.signup_form.newsletter_subscription.after') !!}

                    @if(
                        core()->getConfigData('general.gdpr.settings.enabled')
                        && core()->getConfigData('general.gdpr.agreement.enabled')
                    )
                        <div class="mb-2 flex select-none items-center gap-1.5">
                            <x-shop::form.control-group.control
                                type="checkbox"
                                name="agreement"
                                id="agreement"
                                value="0"
                                rules="required"
                                for="agreement"
                            />

                            <label
                                class="cursor-pointer select-none text-base text-zinc-500 max-sm:text-sm"
                                for="agreement"
                            >
                                {{ core()->getConfigData('general.gdpr.agreement.agreement_label') }}
                            </label>

                            @if (core()->getConfigData('general.gdpr.agreement.agreement_content'))
                                <span
                                    class="cursor-pointer text-base text-navyBlue max-sm:text-sm"
                                    @click="$refs.termsModal.open()"
                                >
                                    @lang('b2b_suite::app.shop.companies.signup-form.click-here')
                                </span>
                            @endif
                        </div>

                        <x-shop::form.control-group.error control-name="agreement" />
                    @endif

                    <div class="mt-8 flex flex-wrap items-center gap-9 max-sm:justify-center max-sm:gap-5">
                        <!-- Save Button -->
                        <button
                            class="primary-button m-0 mx-auto block w-full max-w-[374px] rounded-2xl px-11 py-4 text-center text-base ltr:ml-0 rtl:mr-0 max-md:max-w-full max-md:rounded-lg max-md:py-3 max-sm:py-1.5"
                            type="submit"
                        >
                            @lang('b2b_suite::app.shop.companies.signup-form.button-title')
                        </button>

                        <div class="flex flex-wrap gap-4">
                            {!! view_render_event('bagisto.shop.customers.login_form_controls.after') !!}
                        </div>
                    </div>

                    {!! view_render_event('bagisto.shop.customers.signup_form_controls.after') !!}

                </x-shop::form>
            </div>

			<p class="mt-5 font-medium text-zinc-500 max-sm:text-center max-sm:text-sm">
                @lang('b2b_suite::app.shop.companies.signup-form.account-exists')

                <a class="text-navyBlue"
                    href="{{ route('shop.customer.session.index') }}"
                >
                    @lang('b2b_suite::app.shop.companies.signup-form.sign-in-button')
                </a>
            </p>
		</div>

        <p class="mb-4 mt-8 text-center text-xs text-zinc-500">
            @lang('b2b_suite::app.shop.companies.signup-form.footer', ['current_year'=> date('Y') ])
        </p>
	</div>

    @push('scripts')
        {!! \Webkul\Customer\Facades\Captcha::renderJS() !!}
    @endpush

    <!-- Terms & Conditions Modal -->
    <x-shop::modal ref="termsModal">
        <x-slot:toggle></x-slot>

        <x-slot:header class="!p-5">
            <p>@lang('b2b_suite::app.shop.companies.signup-form.terms-conditions')</p>
        </x-slot>

        <x-slot:content class="!p-5">
            <div class="max-h-[500px] overflow-auto">
                {!! core()->getConfigData('general.gdpr.agreement.agreement_content') !!}
            </div>
        </x-slot>
    </x-admin::modal>
</x-shop::layouts>
