<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\View\View;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Shop\Http\Controllers\Customer\Account\OrderController as BaseOrderController;

class OrderController extends BaseOrderController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        protected CustomerRepository $customerRepository,
        protected CustomerQuoteRepository $customerQuoteRepository,
    ) {
        parent::__construct($orderRepository, $invoiceRepository);
    }

    /**
     * Show the order. Widened for B2B so a company member may also view an order that backs one
     * of their company's purchase orders — a purchase order is a company document, so the sales
     * order behind it must be visible to every member who can see the PO (not only the member
     * who placed it). Personal orders stay private to their owner.
     *
     * @param  int  $id
     * @return View
     */
    public function view($id)
    {
        $order = $this->orderRepository->findOneWhere([
            'customer_id' => auth()->guard('customer')->id(),
            'id' => $id,
        ]);

        if (! $order && $this->isCompanyPurchaseOrder($id)) {
            $order = $this->orderRepository->find($id);
        }

        abort_if(! $order, 404);

        return view('shop::customers.account.orders.view', compact('order'));
    }

    /**
     * Whether the given order backs a purchase order of the current member's company and the
     * member is permitted to see purchase orders.
     */
    protected function isCompanyPurchaseOrder($orderId): bool
    {
        if (! customer_bouncer()->hasPermission('account.purchase_orders')) {
            return false;
        }

        if (! $companyId = $this->resolveCompanyId()) {
            return false;
        }

        return $this->customerQuoteRepository
            ->findWhere([
                'order_id' => $orderId,
                'company_id' => $companyId,
                'state' => CustomerQuote::STATE_PURCHASE_ORDER,
            ])
            ->isNotEmpty();
    }

    /**
     * Resolve the company the current customer belongs to — their own id when they are the
     * company account, the linked company for a member, or null for a plain customer.
     */
    protected function resolveCompanyId(): ?int
    {
        $customer = $this->customerRepository->find(auth()->guard('customer')->id());

        if (! $customer) {
            return null;
        }

        if ($customer->type === 'company') {
            return $customer->id;
        }

        return $customer->companies()->first()?->id;
    }
}
