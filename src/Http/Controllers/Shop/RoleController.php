<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\B2BSuite\DataGrids\Shop\RoleDataGrid;
use Webkul\B2BSuite\Repositories\CompanyRoleRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
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
            return datagrid(RoleDataGrid::class)->process();
        }

        return view('b2b_suite::shop.customers.account.roles.index');
    }

    /**
     * Show the user create form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('b2b_suite::shop.customers.account.roles.create');
    }

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $this->validate(request(), [
            'name'            => 'required',
            'permission_type' => 'required|in:all,custom',
            'description'     => 'required',
        ]);

        if (request('permission_type') == 'custom') {
            $this->validate(request(), [
                'permissions' => 'required',
            ]);
        }

        Event::dispatch('customer.role.create.before');

        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        if ($currentAdmin->type === 'company') {
            $companyId = $currentAdmin->id;
        } else {
            $company = $currentAdmin->companies()->first();

            $companyId = $company ? $company->id : $currentAdmin->id;
        }

        $data = array_merge(request()->only([
            'name',
            'description',
            'permission_type',
            'permissions',
        ]), [
            'customer_id' => $companyId,
        ]);

        $role = $this->companyRoleRepository->create($data);

        Event::dispatch('customer.role.create.after', $role);

        session()->flash('success', trans('b2b_suite::app.shop.customers.account.roles.create-success'));

        return redirect()->route('shop.customers.account.roles.index');
    }

    /**
     * For loading the edit form page.
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $role = $this->companyRoleRepository->findOrFail($id);

        return view('b2b_suite::shop.customers.account.roles.edit', compact('role'));
    }

    /**
     * Edit function for editing customer profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate(request(), [
            'name'            => 'required',
            'permission_type' => 'required|in:all,custom',
            'description'     => 'required',
        ]);

        /**
         * Check for other customers if the role has been changed from all to custom.
         */
        $isChangedFromAll = request('permission_type') == 'custom' && $this->companyRoleRepository->find($id)->permission_type == 'all';

        if (
            $isChangedFromAll
            && $this->companyRoleRepository->countCustomersWithAllAccess() === 1
        ) {
            session()->flash('error', trans('b2b_suite::app.shop.customers.account.roles.being-used'));

            return redirect()->route('shop.customers.account.roles.index');
        }

        $data = array_merge(request()->only([
            'name',
            'description',
            'permission_type',
        ]), [
            'permissions' => request()->has('permissions') ? request('permissions') : [],
            'customer_id' => auth()->guard('customer')->user()->id,
        ]);

        Event::dispatch('customer.role.update.before', $id);

        $role = $this->companyRoleRepository->update($data, $id);

        Event::dispatch('customer.role.update.after', $role);

        session()->flash('success', trans('b2b_suite::app.shop.customers.account.roles.update-success'));

        return redirect()->route('shop.customers.account.roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $role = $this->companyRoleRepository->findOrFail($id);

        if ($role->customers->count() >= 1) {
            return new JsonResponse(['message' => trans('admin::app.settings.roles.being-used', [
                'name'   => 'admin::app.settings.roles.index.title',
                'source' => 'admin::app.settings.roles.index.admin-user',
            ])], 400);
        }

        if ($this->companyRoleRepository->count() == 1) {
            return new JsonResponse([
                'message' => trans(
                    'admin::app.settings.roles.last-delete-error'
                ),
            ], 400);
        }

        try {
            Event::dispatch('customer.role.delete.before', $id);

            $this->companyRoleRepository->delete($id);

            Event::dispatch('customer.role.delete.after', $id);

            return new JsonResponse(['message' => trans('b2b_suite::app.shop.customers.account.roles.delete-success')]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans(
                'shop.customers.account.roles.delete-failed'
            ),
        ], 500);
    }
}
