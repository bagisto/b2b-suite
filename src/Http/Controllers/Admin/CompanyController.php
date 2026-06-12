<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\B2BSuite\DataGrids\Admin\CompanyDataGrid;
use Webkul\B2BSuite\Http\Requests\CompanyRequest;
use Webkul\B2BSuite\Http\Resources\CustomerResource;
use Webkul\B2BSuite\Repositories\CompanyAttributeGroupRepository;
use Webkul\B2BSuite\Repositories\CompanyAttributeValueRepository;
use Webkul\B2BSuite\Repositories\CompanyFlatRepository;
use Webkul\B2BSuite\Repositories\CompanyRoleRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CompanyFlatRepository $customerFlatRepository,
        protected CompanyAttributeGroupRepository $companyAttributeGroupRepository,
        protected CompanyAttributeValueRepository $companyAttributeValueRepository,
        protected CompanyRoleRepository $companyRoleRepository
    ) {}

    /**
     * Display a listing of the companies.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(CompanyDataGrid::class)->process();
        }

        return view('b2b_suite::admin.companies.index');
    }

    /**
     * Get companies for autocomplete.
     */
    public function get(): JsonResponse
    {
        $companies = $this->customerRepository
            ->findWhere(['type' => 'company'])
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->first_name.' '.$company->last_name,
                    'email' => $company->email,
                ];
            });

        return new JsonResponse($companies);
    }

    /**
     * Result of search companies/customers.
     */
    public function search(): JsonResource
    {
        $data = request()->all();

        $customers = $this->customerRepository->scopeQuery(function ($query) use ($data) {
            return $query->whereIn('type', [$data['type'] ?? 'company', 'company'])
                ->where(function ($q) use ($data) {
                    $q->where('email', 'like', '%'.urldecode($data['query']).'%')
                        ->orWhere(DB::raw('CONCAT(first_name, " ", last_name)'), 'like', '%'.urldecode($data['query']).'%');
                })
                ->orderBy('created_at', 'desc');
        })->get();

        return CustomerResource::collection($customers);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create()
    {
        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'custom_attributes' => function ($query) {
                $query->orderBy('pivot_position', 'asc');
            },
        ])->orderBy('column', 'asc')->orderBy('position', 'asc')->get();

        return view('b2b_suite::admin.companies.create', compact('attributeGroups'));
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(CompanyRequest $request)
    {
        Event::dispatch('customer.registration.before');

        $password = rand(100000, 10000000);

        $data = array_merge([
            'password' => bcrypt($password),
            'is_verified' => 1,
            /**
             * Companies created from the admin are active by default; the
             * create form may still override this via the status control.
             */
            'status' => $request->input('status') !== null ? (int) (bool) $request->input('status') : 1,
            'channel_id' => core()->getCurrentChannel()->id,
        ], $request->only([
            'first_name',
            'last_name',
            'gender',
            'email',
            'date_of_birth',
            'phone',
            'customer_group_id',
            'channel_id',
            'type',
            'company_role_id',
        ]));

        if (empty($data['customer_group_id'])) {
            $data['customer_group_id'] = core()->getGuestCustomerGroup()->id;
        }

        $customer = $this->customerRepository->create($data);

        $this->companyAttributeValueRepository->saveValues(
            $request->all(),
            $customer,
            $customer->custom_attributes
        );

        $role = $this->companyRoleRepository->create([
            'name' => 'Administrator',
            'description' => 'All permissions',
            'permission_type' => 'all',
            'permissions' => null,
            'customer_id' => $customer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $customer->update(['company_role_id' => $role->id]);

        $customer->companies()->attach($customer->id);

        Event::dispatch('customer.registration.after', $customer);

        return to_route('admin.b2b.companies.index')
            ->withSuccess(trans('b2b_suite::app.admin.companies.create-success'));
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit($id)
    {
        $company = $this->customerRepository->findOrFail($id);

        $attributeGroups = $this->companyAttributeGroupRepository->with([
            'custom_attributes' => function ($query) {
                $query->orderBy('pivot_position', 'asc');
            },
        ])->orderBy('column', 'asc')->orderBy('position', 'asc')->get();

        return view('b2b_suite::admin.companies.edit', compact('company', 'attributeGroups'));
    }

    /**
     * Update the specified company in storage.
     */
    public function update(CompanyRequest $request, $id)
    {
        Event::dispatch('customer.update.before', $id);

        $customer = $this->customerRepository->findOrFail($id);

        $data = array_merge([
            'is_verified' => 1,
            'status' => (int) (bool) $request->input('status'),
            'channel_id' => core()->getCurrentChannel()->id,
        ], $request->only([
            'first_name',
            'last_name',
            'gender',
            'email',
            'date_of_birth',
            'phone',
            'customer_group_id',
            'channel_id',
            'type',
            'company_role_id',
        ]));

        if (empty($data['customer_group_id'])) {
            $data['customer_group_id'] = core()->getGuestCustomerGroup()->id;
        }

        $wasPending = ! $customer->status;

        $customer->update($data);

        if ($wasPending && $customer->status) {
            Event::dispatch('b2b.company.approved', $customer);
        }

        $this->companyAttributeValueRepository->saveValues(
            $request->all(),
            $customer,
            $customer->custom_attributes
        );

        $customer = $customer->refresh();

        Event::dispatch('customer.update.after', $customer);

        return to_route('admin.b2b.companies.index')
            ->withSuccess(trans('b2b_suite::app.admin.companies.update-success'));
    }

    /**
     * Approve (activate) or disable a single company.
     *
     * A pending company has `status = 0`, which Bagisto uses to block login,
     * checkout and protected routes. Approving sets `status = 1`.
     */
    public function updateStatus($id): JsonResponse
    {
        $company = $this->customerRepository->findOrFail($id);

        $status = (int) (bool) request()->input('status', 1);

        $company->update(['status' => $status]);

        Event::dispatch('b2b.company.'.($status ? 'approved' : 'disabled'), $company);

        return new JsonResponse([
            'message' => trans('b2b_suite::app.admin.companies.'.($status ? 'approve-success' : 'disable-success')),
        ]);
    }

    /**
     * Mass approve/disable companies.
     */
    public function massUpdateStatus(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $status = (int) (bool) $massUpdateRequest->input('value');

        $companies = $this->customerRepository->findWhereIn('id', $massUpdateRequest->input('indices'));

        foreach ($companies as $company) {
            $company->update(['status' => $status]);

            Event::dispatch('b2b.company.'.($status ? 'approved' : 'disabled'), $company);
        }

        return new JsonResponse([
            'message' => trans('b2b_suite::app.admin.companies.mass-update-status-success'),
        ]);
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy($id)
    {
        try {
            $this->customerRepository->delete($id);

            return response()->json([
                'message' => trans('b2b_suite::app.admin.companies.delete-success'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('b2b_suite::app.admin.companies.delete-failed'),
            ], 500);
        }
    }

    /**
     * Remove the specified resources from database.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $customers = $this->customerRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        try {
            /**
             * Ensure that customers do not have any active orders before performing deletion.
             */
            foreach ($customers as $customer) {
                if ($this->customerRepository->haveActiveOrders($customer)) {
                    throw new \Exception(trans('admin::app.customers.customers.index.datagrid.order-pending'));
                }
            }

            /**
             * After ensuring that they have no active orders delete the corresponding customer.
             */
            foreach ($customers as $customer) {
                Event::dispatch('customer.delete.before', $customer);

                $this->customerRepository->delete($customer->id);

                Event::dispatch('customer.delete.after', $customer);
            }

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.companies.mass-delete-success'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.companies.delete-failed'),
            ], 500);
        }
    }
}
