{{--
    Separate "Company Information" card on the storefront Account page, injected after the
    personal "Profile Information" fields (via bagisto.shop.customers.account.profile.email.after).
    Shown only when the logged-in customer belongs to a company. Keeping it as its own card —
    with its own Edit (permission-gated) and View links — keeps the personal profile's Edit
    button unambiguous. No data is passed by the event, so the customer is resolved from the
    guard and re-fetched through the repository to get the bound B2B model.
--}}
@php
    $b2bAuthId = auth()->guard('customer')->user()?->id;

    $b2bCustomer = $b2bAuthId
        ? app(\Webkul\Customer\Repositories\CustomerRepository::class)->find($b2bAuthId)
        : null;

    $b2bCreditManager = app(\Webkul\B2BSuite\Helpers\CreditManager::class);

    $b2bCompany = $b2bCustomer ? $b2bCreditManager->companyOf($b2bCustomer) : null;
@endphp

@if ($b2bCompany)
    @php
        $b2bBusinessName = method_exists($b2bCompany, 'businessName') ? $b2bCompany->businessName() : null;

        $b2bRole = $b2bCustomer->company_role_id
            ? \Webkul\B2BSuite\Models\CompanyRoleProxy::modelClass()::find($b2bCustomer->company_role_id)
            : null;

        $b2bCredit = $b2bCreditManager->isActive() ? $b2bCreditManager->companyCreditFor($b2bCustomer) : null;

        /**
         * Credit is shown here only to members whose role grants the company-credit
         * permission — mirroring the dedicated credit page guard, so it can't leak to a
         * member who isn't allowed to see company credit.
         */
        $b2bShowCredit = $b2bCredit
            && $b2bCredit->status
            && customer_bouncer()->hasPermission('account.company_credit');

        $b2bAvailable = $b2bShowCredit ? $b2bCreditManager->availableCredit($b2bCredit) : null;

        $b2bCanViewCompany = customer_bouncer()->hasPermission('account.company_profile');

        $b2bCanEditCompany = customer_bouncer()->hasPermission('account.company_profile.edit');
    @endphp

    <div class="rounded-xl border border-zinc-200 bg-white">
        <!-- Header: title + (Edit if permitted) + View -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 px-6 py-4 max-sm:px-4">
            <h3 class="text-lg font-medium text-zinc-800">
                @lang('b2b::app.shop.customers.account.profile.company-information')
            </h3>

            <div class="flex flex-wrap items-center gap-3">
                @if ($b2bCanEditCompany)
                    <a
                        href="{{ route('shop.companies.account.profile.edit') }}"
                        class="secondary-button border-zinc-200 px-5 py-2 text-sm font-normal"
                    >
                        @lang('b2b::app.shop.customers.account.profile.edit-company')
                    </a>
                @endif

                @if ($b2bCanViewCompany)
                    <a
                        href="{{ route('shop.companies.account.profile.index') }}"
                        class="secondary-button border-zinc-200 px-5 py-2 text-sm font-normal"
                    >
                        @lang('b2b::app.shop.customers.account.profile.view-company-profile')
                    </a>
                @endif
            </div>
        </div>

        <!-- Fields -->
        <div class="px-6 max-sm:px-4">
            <!-- Business Name -->
            <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-100 py-3">
                <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.business-name')</p>
                <p class="text-sm font-medium text-zinc-500">{{ $b2bBusinessName ?: $b2bCompany->name }}</p>
            </div>

            <!-- Your Role -->
            <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-100 py-3">
                <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.role')</p>
                <p class="text-sm font-medium text-zinc-500">{{ $b2bRole?->name ?? '—' }}</p>
            </div>

            <!-- Company Email -->
            <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-100 py-3">
                <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.company-email')</p>
                <p class="text-sm font-medium text-zinc-500">{{ $b2bCompany->email ?: '—' }}</p>
            </div>

            <!-- Company Phone -->
            <div class="grid w-full grid-cols-[2fr_3fr] {{ $b2bShowCredit ? 'border-b border-zinc-100' : '' }} py-3">
                <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.company-phone')</p>
                <p class="text-sm font-medium text-zinc-500">{{ $b2bCompany->phone ?: '—' }}</p>
            </div>

            @if ($b2bShowCredit)
                <!-- Credit Limit -->
                <div class="grid w-full grid-cols-[2fr_3fr] border-b border-zinc-100 py-3">
                    <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.credit-limit')</p>
                    <p class="text-sm font-medium text-zinc-500">{{ core()->formatBasePrice($b2bCredit->credit_limit) }}</p>
                </div>

                <!-- Available Credit -->
                <div class="grid w-full grid-cols-[2fr_3fr] py-3">
                    <p class="text-sm font-medium">@lang('b2b::app.shop.customers.account.profile.available-credit')</p>
                    <p class="text-sm font-medium {{ $b2bAvailable > 0 ? 'text-green-700' : 'text-zinc-500' }}">{{ core()->formatBasePrice($b2bAvailable) }}</p>
                </div>
            @endif
        </div>
    </div>
@endif
