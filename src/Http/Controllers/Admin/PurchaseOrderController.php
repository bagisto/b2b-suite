<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\B2BSuite\DataGrids\Admin\CustomerPurchaseOrderDataGrid;
use Webkul\B2BSuite\Models\Customer;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;

class PurchaseOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CustomerQuoteRepository $customerQuoteRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerPurchaseOrderDataGrid::class)->process();
        }

        return view('b2b::admin.purchase-orders.index');
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return View
     */
    public function view(int $id)
    {
        $quote = $this->customerQuoteRepository
            ->with(['company', 'company.salesRep', 'company.company_flats'])
            ->findOrFail($id);

        abort_unless(Customer::repCanAccessCompany($quote->company_id), 403);

        return view('b2b::admin.purchase-orders.view', compact('quote'));
    }
}
