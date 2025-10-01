<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop\Customer;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        if (
            ! (bool) core()->getConfigData('b2b_suite.general.settings.active')
            || $customer->type != 'company'
        ) {
            return view('shop::customers.account.profile.index', compact('customer'));
        }

        $attributes = $this->companyAttributeRepository->getSignUpAttributes();

        return view('shop::customers.account.profile.index', compact('customer'));
    }

    /**
     * For loading the edit form page.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        if (
            ! (bool) core()->getConfigData('b2b_suite.general.settings.active')
            || $customer->type != 'company'
        ) {
            return view('shop::customers.account.profile.edit', compact('customer'));
        }

        $attributeGroups = $this->companyAttributeGroupRepository->with('custom_attributes')->all();

        return view('b2b_suite::shop.companies.account.profile.edit')
            ->with([
                'customer'        => $customer,
                'attributeGroups' => $attributeGroups,
            ]);
    }

    /**
     * Edit function for editing customer profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function modify(CompanyRequest $request, int $id)
    {
        $this->validate($request, [
            'new_password'                => 'confirmed|min:6|required_with:current_password',
            '`new_password_confirmation`' => 'required_with:new_password',
            'current_password'            => 'required_with:new_password',
            'image'                       => 'array',
            'image.*'                     => 'mimes:bmp,jpeg,jpg,png,webp',
        ]);

        $isPasswordChanged = false;

        $id = auth()->guard('customer')->user()->id;

        Event::dispatch('customer.update.before', $id);

        $customer = $this->customerRepository->findOrFail($id);

        $data = array_merge([
            'is_verified' => 1,
            'channel_id'  => core()->getCurrentChannel()->id,
        ], $request->only([
            'first_name',
            'last_name',
            'gender',
            'email',
            'date_of_birth',
            'phone',
            'customer_group_id',
            'channel_id',
            'slug',
            'type',
            'company_role_id',
            'new_password',
            'new_password_confirmation',
            'current_password',
            'image',
            'subscribed_to_news_letter',
        ]));

        if (
            core()->getCurrentChannel()->theme === 'default'
            && ! isset($data['image'])
        ) {
            $data['image']['image_0'] = '';
        }

        if (! empty($data['current_password'])) {
            if (Hash::check($data['current_password'], auth()->guard('customer')->user()->password)) {
                $isPasswordChanged = true;

                $data['password'] = bcrypt($data['new_password']);
            } else {
                session()->flash('warning', trans('shop::app.customers.account.profile.index.unmatched'));

                return redirect()->back();
            }
        } else {
            unset($data['new_password']);
        }

        Event::dispatch('customer.update.before', $id);

        if ($customer = $this->customerRepository->update($data, auth()->guard('customer')->user()->id)) {
            if ($isPasswordChanged) {
                Event::dispatch('customer.password.update.after', $customer);
            }

            $this->companyAttributeValueRepository->saveValues(
                $request->all(),
                $customer,
                $customer->custom_attributes
            );

            $customer = $customer->refresh();

            Event::dispatch('customer.update.after', $customer);

            if ($data['subscribed_to_news_letter']) {
                $subscription = $this->subscriptionRepository->findOneWhere(['email' => $data['email']]);

                if ($subscription) {
                    $this->subscriptionRepository->update([
                        'customer_id'   => $customer->id,
                        'is_subscribed' => 1,
                    ], $subscription->id);
                } else {
                    $this->subscriptionRepository->create([
                        'email'         => $data['email'],
                        'customer_id'   => $customer->id,
                        'channel_id'    => core()->getCurrentChannel()->id,
                        'is_subscribed' => 1,
                        'token'         => $token = uniqid(),
                    ]);
                }
            } else {
                $subscription = $this->subscriptionRepository->findOneWhere(['email' => $data['email']]);

                if ($subscription) {
                    $this->subscriptionRepository->update([
                        'customer_id'   => $customer->id,
                        'is_subscribed' => 0,
                    ], $subscription->id);
                }
            }

            if (request()->hasFile('image')) {
                $this->customerRepository->uploadImages($data, $customer);
            } else {
                if (isset($data['image'])) {
                    if (! empty($data['image'])) {
                        Storage::delete((string) $customer->image);
                    }

                    $customer->image = null;

                    $customer->save();
                }
            }

            return to_route('shop.customers.account.profile.index')
                ->withSuccess(trans('shop::app.customers.account.profile.index.edit-success'));
        }

        session()->flash('success', trans('shop::app.customer.account.profile.edit-fail'));

        return redirect()->back('shop.customers.account.profile.edit');
    }
}
