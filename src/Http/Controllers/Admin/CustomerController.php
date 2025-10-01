<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Customers\CustomerController as BaseCustomerController;
use Webkul\B2BSuite\DataGrids\Admin\Customers\CustomerDataGrid;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerNoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class CustomerController extends BaseCustomerController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected CustomerNoteRepository $customerNoteRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerDataGrid::class)->process();
        }

        $groups = $this->customerGroupRepository->findWhere([['code', '<>', 'guest']]);

        return view('admin::customers.customers.index', compact('groups'));
    }
}
