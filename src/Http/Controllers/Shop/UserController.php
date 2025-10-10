<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Admin\Mail\Customer\NewCustomerNotification;
use Webkul\B2BSuite\DataGrids\Shop\UserDataGrid;
use Webkul\B2BSuite\Repositories\CompanyRoleRepository;
use Webkul\Core\Rules\PhoneNumber;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Sales\Models\Order;
use Webkul\Shop\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected CompanyRoleRepository $companyRoleRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(UserDataGrid::class)->process();
        }

        return view('b2b_suite::shop.customers.account.users.index');
    }

    /**
     * Show the user create form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $customer = auth()->guard('customer')->user();
       
        $currentRole = $this->companyRoleRepository->find($customer->company_role_id);

        $companyAdminId = $currentRole->customer_id;

        $roles = $this->companyRoleRepository->findWhere([
            'customer_id' => $companyAdminId,
        ]);

        return view('b2b_suite::shop.customers.account.users.create', compact('roles'));
    }

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'first_name'       => ['required'],
            'last_name'        => ['required'],
            'email'            => 'email|unique:customers,email',
            'gender'           => 'required|in:Other,Male,Female',
            'date_of_birth'    => 'date|before:today',
            'image'            => 'array',
            'image.*'          => 'mimes:bmp,jpeg,jpg,png,webp',
            'phone'            => ['required', new PhoneNumber, 'unique:customers,phone'],
            'company_role_id'  => ['required'],
        ]);

        $customerGroup = core()->getConfigData('customer.settings.create_new_account_options.default_group');

        $password = rand(100000, 10000000);

        $data = array_merge($request->only([
            'first_name',
            'last_name',
            'email',
            'gender',
            'date_of_birth',
            'phone',
            'image',
            'company_role_id',
        ]), [
            'type'              => 'user',
            'password'          => bcrypt($password),
            'api_token'         => Str::random(80),
            'is_verified'       => ! core()->getConfigData('customer.settings.email.verification'),
            'customer_group_id' => $this->customerGroupRepository->findOneWhere(['code' => $customerGroup])->id,
            'channel_id'        => core()->getCurrentChannel()->id,
            'token'             => md5(uniqid(rand(), true)),
        ]);

        if (
            core()->getCurrentChannel()->theme === 'default'
            && ! isset($data['image'])
        ) {
            $data['image']['image_0'] = '';
        }

        if (empty($data['date_of_birth'])) {
            unset($data['date_of_birth']);
        }

        Event::dispatch('customer.registration.before');

        $customer = $this->customerRepository->create($data);

        $customer->companies()->sync([auth()->guard('customer')->user()->id]);

        Event::dispatch('customer.create.after', $customer);

        Event::dispatch('customer.registration.after', $customer);

        if (request()->hasFile('image')) {
            $this->customerRepository->uploadImages($data, $customer);
        }

        if (core()->getConfigData('emails.general.notifications.emails.general.notifications.customer_account_credentials')) {
            try {
                Mail::queue(new NewCustomerNotification($customer, $password));
            } catch (\Exception $e) {
                report($e);
            }
        }

        if (core()->getConfigData('emails.general.notifications.emails.general.notifications.verification')) {
            session()->flash('success', trans('shop::app.customers.signup-form.success-verify'));
        } else {
            session()->flash('success', trans('b2b_suite::app.shop.customers.account.users.create-success'));
        }

        return redirect()->route('shop.customers.account.users.index');
    }

    /**
     * For loading the edit form page.
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = $this->customerRepository->find($id);
        $loggedInCustomer = auth()->guard('customer')->user();

        $currentRole = $this->companyRoleRepository->find($loggedInCustomer->company_role_id);

        $companyAdminId = $currentRole->customer_id;

        $roles = $this->companyRoleRepository->findWhere([
            'customer_id' => $companyAdminId,
        ]);

        return view('b2b_suite::shop.customers.account.users.edit', compact('user', 'roles'));
    }

    /**
     * Edit function for editing customer profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name'       => ['required'],
            'last_name'        => ['required'],
            'email'            => 'email|unique:customers,email,'.$id,
            'gender'           => 'required|in:Other,Male,Female',
            'date_of_birth'    => 'date|before:today',
            'image'            => 'array',
            'image.*'          => 'mimes:bmp,jpeg,jpg,png,webp',
            'phone'            => ['required', new PhoneNumber, 'unique:customers,phone,'.$id],
            'company_role_id'  => ['required'],
        ]);

        $data = array_merge($request->only([
            'first_name',
            'last_name',
            'email',
            'gender',
            'date_of_birth',
            'phone',
            'image',
            'company_role_id',
        ]), [
            'type' => 'user',
        ]);

        if (
            core()->getCurrentChannel()->theme === 'default'
            && ! isset($data['image'])
        ) {
            $data['image']['image_0'] = '';
        }

        if (empty($data['date_of_birth'])) {
            unset($data['date_of_birth']);
        }

        Event::dispatch('customer.update.before');

        if ($customer = $this->customerRepository->update($data, $id)) {
            Event::dispatch('customer.update.after', $customer);

            if (request()->hasFile('image')) {
                $this->customerRepository->uploadImages($data, $customer);
            } else {
                if (
                    isset($data['image'])
                    && ! empty($data['image'])
                    && ! isset($data['image']['image_0'])
                ) {
                    Storage::delete((string) $customer->image);

                    $customer->image = null;

                    $customer->save();
                }
            }

            session()->flash('success', trans('b2b_suite::app.shop.customers.account.users.edit-success'));

            return redirect()->route('shop.customers.account.users.index');
        }

        session()->flash('success', trans('b2b_suite::app.shop.customers.account.users.edit-fail'));

        return redirect()->back('shop.customers.account.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResource
    {
        $companyId = auth()->guard('customer')->user()->id;

        $user = $this->customerRepository->findOneWhere([
            'id'    => $id,
            'type'  => 'user',
        ]);

        if (
            ! $user
            || ! $user->companies->contains($companyId)
        ) {
            return new JsonResource([
                'message' => trans('b2b_suite::app.shop.customers.account.users.un-auth-access'),
            ], 401);
        }

        try {
            if ($user->orders->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])->first()) {
                session()->flash('error', trans('shop::app.customers.account.profile.index.order-pending'));

                return new JsonResource([
                    'message' => trans('shop::app.customers.account.profile.index.order-pending'),
                ], 422);
            }

            $this->customerRepository->delete($id);

            return new JsonResource([
                'message' => trans('b2b_suite::app.shop.customers.account.users.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResource([
                'message' => trans('b2b_suite::app.shop.customers.account.users.delete-failed'),
            ], 500);
        }
    }
}
