<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Webkul\B2BSuite\DataGrids\Shop\CustomerPurchaseOrderDataGrid;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteMessageRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Http\Controllers\Controller;

class PurchaseOrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerQuoteRepository $customerQuoteRepository,
        protected CustomerQuoteMessageRepository $customerQuoteMessageRepository,
    ) {}

    /**
     * Populate the request for quote page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerPurchaseOrderDataGrid::class)->process();
        }

        return view('b2b_suite::shop.customers.account.purchase-orders.index');
    }

    /**
     * For loading the edit form page.
     *
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        $quote = $this->customerQuoteRepository->with(['company', 'agent', 'attachments'])->findOneWhere([
            'id'          => $id,
            'customer_id' => $customer->id,
            'state'       => CustomerQuote::STATE_PURCHASE_ORDER,
        ]);

        if (! $quote) {
            abort(404);
        }

        $isAdminLastQuotation = $this->customerQuoteMessageRepository->getLastQuotationMessage($quote->id, 'admin');

        return view('b2b_suite::shop.customers.account.purchase-orders.view', compact('customer', 'quote', 'isAdminLastQuotation'));
    }
}
