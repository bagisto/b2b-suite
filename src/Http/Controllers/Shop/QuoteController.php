<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\B2BSuite\DataGrids\Shop\CustomerQuoteDataGrid;
use Webkul\B2BSuite\Http\Requests\QuoteRequest;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteAttachmentRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteMessageRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteQuotationRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\User\Repositories\AdminRepository;

class QuoteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected AdminRepository $adminRepository,
        protected CustomerQuoteRepository $customerQuoteRepository,
        protected CustomerQuoteMessageRepository $customerQuoteMessageRepository,
        protected CustomerQuoteQuotationRepository $customerQuoteQuotationRepository,
        protected CustomerQuoteAttachmentRepository $customerQuoteAttachmentRepository,
    ) {}

    /**
     * Populate the request for quote page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerQuoteDataGrid::class)->process();
        }

        return view('b2b_suite::shop.customers.account.quotes.index');
    }

    /**
     * Store a new quote request.
     */
    public function store(QuoteRequest $quoteRequest): JsonResponse
    {
        Event::dispatch('b2b.quote.create.before');

        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        $customerCompany = $customer->companies->first();

        $quoteNumber = $this->customerQuoteRepository->generateQuotationNumber(null);

        $data = array_merge([
            'quotation_number' => $quoteNumber['quotation_number'],
            'po_number'        => $quoteNumber['po_number'],
            'customer_id'      => $customer->id,
            'company_id'       => $customerCompany ? $customerCompany->id : null,
            'agent_id'         => $this->adminRepository->first()?->id ?? null,
            'customer_name'    => $customer->name,
        ], $quoteRequest->only([
            'name',
            'description',
            'status',
            'attachments',
        ]));

        $quote = $this->customerQuoteRepository->create($data);

        Event::dispatch('b2b.quote.create.after', $quote);

        session()->flash('success', trans('b2b_suite::app.shop.checkout.cart.request-quote.create-success'));

        return new JsonResponse([
            'data'         => $quote,
            'redirect_url' => route('shop.customers.account.quotes.view', $quote->id),
        ]);
    }

    /**
     * For loading the edit form page.
     *
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        $quoteConditions = [
            'id'    => $id,
            'state' => CustomerQuote::STATE_QUOTATION,
        ];

        if ($currentAdmin->type === 'company') {
            $quoteConditions['company_id'] = $currentAdmin->id;
        } else {
            $company = $currentAdmin->companies()->first();

            if ($company) {
                $quoteConditions['company_id'] = $company->id;
            } else {
                $quoteConditions['customer_id'] = $currentAdmin->id;
            }
        }

        $quote = $this->customerQuoteRepository->with(['company', 'agent', 'attachments'])->findOneWhere($quoteConditions);

        if (! $quote) {
            session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.not-found'));

            return redirect()->route('shop.customers.account.quotes.index');
        }

        $isAdminLastQuotation = $this->customerQuoteMessageRepository->getLastQuotationMessage($quote->id, 'admin');

        return view('b2b_suite::shop.customers.account.quotes.view', compact('currentAdmin', 'quote', 'isAdminLastQuotation'));
    }

    /**
     * Update quote details.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'order_date'            => 'date|after_or_equal:today',
            'expected_arrival_date' => 'date|after_or_equal:order_date',
        ]);

        Event::dispatch('b2b.quote.update.before', $id);

        $quote = $this->customerQuoteRepository->findOrFail($id);

        $data = $request->only([
            'order_date',
            'expected_arrival_date',
        ]);

        $quote = $this->customerQuoteRepository->update($data, $id);

        Event::dispatch('b2b.quote.update.after', $quote);

        session()->flash('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-updated'));

        return redirect()->back();
    }

    /**
     * AJAX endpoint for loading messages with pagination and filters
     *
     * @param  int  $id  Quote ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages($id, Request $request)
    {
        $quote = $this->customerQuoteRepository->findOrFail($id);

        $query = $quote->messages()
            ->with('quotations', 'quotations.item');

        if ($request->get('has_quotations') === 'true') {
            $query->has('quotations');
        }

        if ($request->get('user_type')) {
            $query->where('user_type', $request->get('user_type'));
        }

        $messages = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($messages);
    }

    /**
     * UpdateCart quote details.
     */
    public function updateCart($id)
    {
        $customerId = auth()->guard('customer')->user()->id;

        try {
            $quote = $this->customerQuoteRepository->findOneWhere([
                'id'          => $id,
                'customer_id' => $customerId,
            ]);

            if (! $quote || $quote->status != CustomerQuote::STATUS_ACCEPTED) {
                session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $this->customerQuoteRepository->updateCart($id);

            return redirect()->route('shop.checkout.cart.index')
                ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-item-updated'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Download a quote attachment.
     */
    public function download($id, $attachmentId)
    {
        $quote = $this->customerQuoteRepository->findOrFail($id);
        $attachment = $this->customerQuoteAttachmentRepository->findOrFail($attachmentId);

        if ($attachment->customer_quote_id != $quote->id) {
            session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));
        }

        $fileName = substr($attachment->path, strrpos($attachment->path, '/') + 1);

        if (Storage::disk('public')->exists($attachment->path)) {
            return Storage::disk('public')->download($attachment->path, $fileName);
        } else {
            session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.no-attachment'));
        }
    }

    /**
     * Submit a quote (Draft to Open).
     */
    public function submitQuote(Request $request, $id)
    {
        $request->validate([
            'items'   => ['required', 'array', 'min:1'],
            'message' => 'required|string|max:1000',
        ]);

        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        try {
            $quoteConditions = ['id' => $id];

            if ($currentAdmin->type === 'company') {
                $quoteConditions['company_id'] = $currentAdmin->id;
            } else {
                $company = $currentAdmin->companies()->first();

                if ($company) {
                    $quoteConditions['company_id'] = $company->id;
                } else {
                    $quoteConditions['customer_id'] = $currentAdmin->id;
                }
            }

            $quote = $this->customerQuoteRepository->findOneWhere($quoteConditions);

            if (! $quote) {
                session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $quoteStatus = $quote->status === CustomerQuote::STATUS_DRAFT
                ? CustomerQuote::STATUS_OPEN
                : CustomerQuote::STATUS_NEGOTIATION;

            $message = $quote->messages()->create([
                'message'    => $request->message,
                'user_type'  => 'customer',
                'user_id'    => $currentAdmin->id,
                'created_at' => now(),
            ]);

            $data = array_merge([
                'status'     => $quoteStatus,
                'message_id' => $message->id,
            ], $request->only(['items', 'message']));

            $this->customerQuoteRepository->createOrUpdateMessageQuotation($data, $id);

            return redirect()
                ->route('shop.customers.account.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-submitted'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Delete a quote (soft_delete).
     */
    public function deleteQuote(Request $request, $id)
    {
        $customerId = auth()->guard('customer')->user()->id;

        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $quote = $this->customerQuoteRepository->findOneWhere([
                'id'          => $id,
                'customer_id' => $customerId,
            ]);

            if (! $quote) {
                session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $quote->soft_deleted = 1;

            $quote->save();

            $quote->messages()->create([
                'message'    => $request->message,
                'user_type'  => 'customer',
                'user_id'    => $customerId,
                'created_at' => now(),
            ]);

            return redirect()->route('shop.customers.account.quotes.index')
                ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-deleted'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Send a message for this quote.
     */
    public function sendMessage(Request $request, $id)
    {
        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $quoteConditions = [
                'id' => $id,
            ];

            if ($currentAdmin->type === 'company') {
                $quoteConditions['company_id'] = $currentAdmin->id;
            } else {
                $company = $currentAdmin->companies()->first();

                if ($company) {
                    $quoteConditions['company_id'] = $company->id;
                } else {
                    $quoteConditions['customer_id'] = $currentAdmin->id;
                }
            }

            $quote = $this->customerQuoteRepository->findOneWhere($quoteConditions);

            if (! $quote) {
                session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $quote->messages()->create([
                'message'    => $request->message,
                'user_type'  => 'customer',
                'user_id'    => $currentAdmin->id,
                'created_at' => now(),
            ]);

            return redirect()->route('shop.customers.account.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.success-message'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => trans('b2b_suite::app.shop.customers.account.quotes.view.error-message')]);
        }
    }

    /**
     * Accept this quote.
     */
    public function acceptQuote(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        $quoteConditions = ['id' => $id];

        if ($currentAdmin->type === 'company') {
            $quoteConditions['company_id'] = $currentAdmin->id;
        } else {
            $company = $currentAdmin->companies()->first();

            if ($company) {
                $quoteConditions['company_id'] = $company->id;
            } else {
                $quoteConditions['customer_id'] = $currentAdmin->id;
            }
        }

        $quote = $this->customerQuoteRepository->findOneWhere($quoteConditions);

        if (! $quote) {
            session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

            return redirect()->back();
        }

        $quote->update(['status' => CustomerQuote::STATUS_ACCEPTED]);

        $quote->messages()->create([
            'message'    => $request->message,
            'status'     => trans('b2b_suite::app.shop.customers.account.quotes.view.'.$quote->status),
            'user_type'  => 'customer',
            'user_id'    => $currentAdmin->id,
            'created_at' => now(),
        ]);

        $isAdminLastQuotation = $this->customerQuoteMessageRepository
            ->getLastQuotationMessage($quote->id, 'admin');

        $this->customerQuoteQuotationRepository->updateOrCreate(
            [
                'message_id' => $isAdminLastQuotation?->id,
                'quote_id'   => $quote->id,
            ],
            [
                'is_accepted' => 1,
                'accepted_by' => 'customer',
            ]
        );

        return redirect()
            ->route('shop.customers.account.quotes.view', $id)
            ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-accepted'));
    }

    /**
     * Reject a quote (Open to Rejected) by customer.
     */
    public function rejectQuote(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $currentAdmin = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        try {
            $quoteConditions = ['id' => $id];

            if ($currentAdmin->type === 'company') {
                $quoteConditions['company_id'] = $currentAdmin->id;
            } else {
                $company = $currentAdmin->companies()->first();

                if ($company) {
                    $quoteConditions['company_id'] = $company->id;
                } else {
                    $quoteConditions['customer_id'] = $currentAdmin->id;
                }
            }

            $quote = $this->customerQuoteRepository->findOneWhere($quoteConditions);

            if (! $quote) {
                session()->flash('error', trans('b2b_suite::app.shop.customers.account.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $quote->update(['status' => CustomerQuote::STATUS_REJECTED]);

            $quote->messages()->create([
                'message'    => $request->message,
                'status'     => trans('b2b_suite::app.shop.customers.account.quotes.view.'.$quote->status),
                'user_type'  => 'customer',
                'user_id'    => $currentAdmin->id,
                'created_at' => now(),
            ]);

            return redirect()
                ->route('shop.customers.account.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.shop.customers.account.quotes.view.quote-rejected'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }
}
