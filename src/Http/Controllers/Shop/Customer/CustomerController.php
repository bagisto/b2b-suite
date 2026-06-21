<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop\Customer;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\B2BSuite\DataGrids\Shop\CompanyCreditTransactionDataGrid;
use Webkul\B2BSuite\Helpers\CreditManager;
use Webkul\B2BSuite\Http\Requests\CompanyRequest;
use Webkul\B2BSuite\Repositories\CompanyAttributeGroupRepository;
use Webkul\B2BSuite\Repositories\CompanyAttributeRepository;
use Webkul\B2BSuite\Repositories\CompanyAttributeValueRepository;
use Webkul\Core\Repositories\SubscribersListRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductReviewRepository;
use Webkul\Shop\Http\Controllers\Customer\CustomerController as BaseCustomerController;

class CustomerController extends BaseCustomerController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected ProductReviewRepository $productReviewRepository,
        protected CompanyAttributeRepository $companyAttributeRepository,
        protected CompanyAttributeGroupRepository $companyAttributeGroupRepository,
        protected CompanyAttributeValueRepository $companyAttributeValueRepository,
        protected SubscribersListRepository $subscriptionRepository
    ) {}

    /**
     * Dedicated Company Profile page — renders the mapped company attributes
     * exactly as configured in the admin (grouped by column/position).
     *
     * @return View
     */
    public function companyProfile()
    {
        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            abort(404);
        }

        /**
         * Access is role-gated: the company account, or members whose role grants the
         * `company_profile` permission. Everyone else (and plain customers) is rejected.
         */
        if (! customer_bouncer()->hasPermission('account.company_profile')) {
            abort(404);
        }

        $customer = $this->resolveCompany();

        if (! $customer) {
            abort(404);
        }

        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'custom_attributes' => function ($query) {
                $query->orderBy('pivot_position', 'asc');
            },
        ])->orderBy('column', 'asc')->orderBy('position', 'asc')->get();

        return view('b2b::shop.customers.account.company-profile.index')
            ->with([
                'customer' => $customer,
                'attributeGroups' => $attributeGroups,
                'canEdit' => customer_bouncer()->hasPermission('account.company_profile.edit'),
            ]);
    }

    /**
     * Edit form for the company profile — separate from the read-only summary view. Only
     * reachable by members whose role grants the edit permission.
     *
     * @return View
     */
    public function companyProfileEdit()
    {
        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            abort(404);
        }

        if (! customer_bouncer()->hasPermission('account.company_profile.edit')) {
            abort(404);
        }

        $customer = $this->resolveCompany();

        if (! $customer) {
            abort(404);
        }

        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'custom_attributes' => function ($query) {
                $query->orderBy('pivot_position', 'asc');
            },
        ])->orderBy('column', 'asc')->orderBy('position', 'asc')->get();

        return view('b2b::shop.customers.account.company-profile.edit')
            ->with([
                'customer' => $customer,
                'attributeGroups' => $attributeGroups,
                'canEdit' => true,
            ]);
    }

    /**
     * Edit function for editing customer profile.
     *
     * @return Response
     */
    public function modify(CompanyRequest $request, int $id)
    {
        if (! (bool) core()->getConfigData('b2b.general.settings.active')) {
            abort(404);
        }

        /**
         * Saving requires the dedicated edit permission (view-only members are blocked).
         */
        if (! customer_bouncer()->hasPermission('account.company_profile.edit')) {
            abort(401, 'Unauthorized action.');
        }

        $customer = $this->resolveCompany();

        /**
         * The edited record must be the caller's own company — never another company
         * passed through the route id.
         */
        if (! $customer || $customer->id !== (int) $id) {
            abort(401, 'Unauthorized action.');
        }

        Event::dispatch('customer.update.before', $customer->id);

        /**
         * Only the column-backed company attributes are written to the customers
         * table; everything else is persisted as company attribute values. Account
         * credentials (password) and the avatar live on the core profile edit form.
         */
        $data = $request->only([
            'first_name',
            'last_name',
            'gender',
            'email',
            'date_of_birth',
            'phone',
        ]);

        $this->customerRepository->update($data, $customer->id);

        $this->companyAttributeValueRepository->saveValues(
            $request->all(),
            $customer,
            $customer->custom_attributes
        );

        $customer = $customer->refresh();

        Event::dispatch('customer.update.after', $customer);

        return to_route('shop.customers.account.company_profile.index')
            ->withSuccess(trans('shop::app.customers.account.profile.index.edit-success'));
    }

    /**
     * Company credit dashboard for the buyer: balance, available credit and ledger.
     *
     * @return View
     */
    public function companyCredit()
    {
        $creditManager = app(CreditManager::class);

        if (
            ! (bool) core()->getConfigData('b2b.general.settings.active')
            || ! $creditManager->isActive()
            || ! customer_bouncer()->hasPermission('account.company_credit')
        ) {
            abort(404);
        }

        if (! $company = $this->resolveCompany()) {
            abort(404);
        }

        $credit = $creditManager->find($company->id);

        if (! $credit) {
            abort(404);
        }

        if (request()->ajax()) {
            return app(CompanyCreditTransactionDataGrid::class)->process();
        }

        return view('b2b::shop.customers.account.company-credit.index')
            ->with(compact('company', 'credit'));
    }

    /**
     * Resolve the company customer whose profile is shown/edited: the account itself for
     * a company login, or the company a member belongs to.
     */
    protected function resolveCompany()
    {
        $authId = auth()->guard('customer')->user()?->id;

        if (! $authId) {
            return null;
        }

        /**
         * Resolve through the repository so we get the B2B customer model (with the
         * `companies()` relation), regardless of the model the auth guard returns.
         */
        $customer = $this->customerRepository->find($authId);

        if (! $customer) {
            return null;
        }

        if ($customer->type === 'company') {
            return $customer;
        }

        $company = $customer->companies()->first();

        return $company ? $this->customerRepository->find($company->id) : null;
    }
}
