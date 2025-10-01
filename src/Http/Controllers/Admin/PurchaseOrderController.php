<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\B2BSuite\DataGrids\Admin\CustomerPurchaseOrderDataGrid;
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerPurchaseOrderDataGrid::class)->process();
        }

        return view('b2b_suite::admin.purchase-orders.index');
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function view(int $id)
    {
        $quote = $this->customerQuoteRepository->findOrFail($id);

        return view('b2b_suite::admin.purchase-orders.view', compact('quote'));
    }
}
