<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop\Customer;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
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
     * Taking the customer to profile details page.
     *
     * @return View
     */
    public function index()
    {
        return parent::index();
    }

    /**
     * For loading the edit form page.
     *
     * The personal account edit form is the core form for every customer
     * (company basic info included). Company-specific mapped attributes are
     * edited on the dedicated Company Profile page (see companyProfile()).
     *
     * @return View
     */
    public function edit()
    {
        return parent::edit();
    }

    /**
     * Dedicated Company Profile page — renders the mapped company attributes
     * exactly as configured in the admin (grouped by column/position).
     *
     * @return View
     */
    public function companyProfile()
    {
        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        if (
            ! (bool) core()->getConfigData('b2b.general.settings.active')
            || $customer->type != 'company'
        ) {
            abort(404);
        }

        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'custom_attributes' => function ($query) {
                $query->orderBy('pivot_position', 'asc');
            },
        ])->orderBy('column', 'asc')->orderBy('position', 'asc')->get();

        return view('b2b::shop.companies.account.profile.index')
            ->with([
                'customer' => $customer,
                'attributeGroups' => $attributeGroups,
            ]);
    }

    /**
     * Edit function for editing customer profile.
     *
     * @return Response
     */
    public function modify(CompanyRequest $request, int $id)
    {
        $id = auth()->guard('customer')->user()->id;

        $customer = $this->customerRepository->findOrFail($id);

        Event::dispatch('customer.update.before', $id);

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
            'company_role_id',
        ]);

        $this->customerRepository->update($data, $id);

        $this->companyAttributeValueRepository->saveValues(
            $request->all(),
            $customer,
            $customer->custom_attributes
        );

        $customer = $customer->refresh();

        Event::dispatch('customer.update.after', $customer);

        return to_route('shop.companies.account.profile.index')
            ->withSuccess(trans('shop::app.customers.account.profile.index.edit-success'));
    }
}
