<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Admin\Http\Resources\CartResource;
use Webkul\B2BSuite\DataGrids\Admin\CustomerQuoteDataGrid;
use Webkul\B2BSuite\Http\Requests\QuoteRequest;
use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\B2BSuite\Repositories\CustomerQuoteMessageRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteQuotationRepository;
use Webkul\B2BSuite\Repositories\CustomerQuoteRepository;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;

class QuoteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected CustomerRepository $customerRepository,
        protected CustomerQuoteRepository $customerQuoteRepository,
        protected CustomerQuoteMessageRepository $customerQuoteMessageRepository,
        protected CustomerQuoteQuotationRepository $customerQuoteQuotationRepository,
        protected CustomerGroupRepository $customerGroupRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CustomerQuoteDataGrid::class)->process();
        }

        $groups = $this->customerGroupRepository->findWhere([['code', '<>', 'guest']]);

        return view('b2b_suite::admin.quotes.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(int $cartId)
    {
        $cart = $this->cartRepository->find($cartId);

        if (! $cart) {
            return redirect()->route('admin.customers.quotes.index');
        }

        $cart = new CartResource($cart);

        $statusLabels = collect(CustomerQuote::statusLabel())->only([
            CustomerQuote::STATUS_OPEN,
            CustomerQuote::STATUS_NEGOTIATION,
            CustomerQuote::STATUS_ACCEPTED,
        ])->toArray();

        $company = $cart->customer->companies->first();

        if ($cart?->company_id) {
            $company = $this->customerRepository->findOneWhere([
                'id'   => $cart->company_id,
                'type' => 'company',
            ]);
        }

        $user = auth()->guard('admin')->user();

        return view('b2b_suite::admin.quotes.create', compact('cart', 'statusLabels', 'company', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(QuoteRequest $quoteRequest)
    {
        Event::dispatch('b2b.quote.create.before');

        $cart = $this->cartRepository->find((int) $quoteRequest->cart_id);

        $company = $this->customerRepository->findOneWhere([
            'id'   => $cart->company_id,
            'type' => 'company',
        ]);

        $customer = $cart->customer;

        $company = $company ? $company->id : $customer->companies->first()->id;

        $quoteNumber = $this->customerQuoteRepository->generateQuotationNumber(null);

        $data = array_merge([
            'quotation_number' => $quoteNumber['quotation_number'],
            'po_number'        => $quoteNumber['po_number'],
            'customer_id'      => $customer->id,
            'company_id'       => $company,
            'agent_id'         => auth()->guard('admin')->user()->id,
            'customer_name'    => $customer->name,
            'cart'             => $cart,
        ], $quoteRequest->only([
            'name',
            'description',
            'status',
            'attachments',
            'order_date',
            'expected_arrival_date',
            'expiration_date',
            'created_at',
        ]));

        $quote = $this->customerQuoteRepository->create($data);

        Event::dispatch('b2b.quote.create.after', $quote);

        session()->flash('success', __('b2b_suite::app.admin.quotes.index.create-success'));

        return redirect()->route('admin.customers.quotes.index');
    }

    /**
     * Show the form for viewing the specified resource.
     *
     * @return \Illuminate\View\View
     */
    public function view(int $id)
    {
        $quote = $this->customerQuoteRepository->findOrFail($id);

        return view('b2b_suite::admin.quotes.view', compact('quote'));
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
     * Send a message for this quote.
     */
    public function sendMessage(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $quote = $this->customerQuoteRepository->findOrFail($id);

            if ($quote->status === CustomerQuote::STATUS_OPEN) {
                $quote->status = CustomerQuote::STATUS_NEGOTIATION;

                $quote->save();
            }

            $quote->messages()->create([
                'message'    => $request->message,
                'user_type'  => 'admin',
                'user_id'    => auth()->guard('admin')->user()->id,
                'created_at' => now(),
            ]);

            return redirect()->route('admin.customers.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.admin.quotes.view.success-message'));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => trans('b2b_suite::app.admin.quotes.view.error-message')]);
        }
    }

    /**
     * Submit a quote (Draft to Open).
     */
    public function submitQuote(Request $request, $id)
    {
        try {
            $request->validate([
                'items'   => ['required', 'array', 'min:1'],
                'message' => 'required|string|max:1000',
            ]);

            $quote = $this->customerQuoteRepository->findOrFail($id);

            $message = $quote->messages()->create([
                'message'    => $request->message,
                'user_type'  => 'admin',
                'user_id'    => auth()->guard('admin')->user()->id,
                'created_at' => now(),
            ]);

            $data = array_merge([
                'status'     => CustomerQuote::STATUS_NEGOTIATION,
                'message_id' => $message->id,
            ], $request->only([
                'items',
                'message',
            ]));

            $this->customerQuoteRepository->createOrUpdateMessageQuotation($data, $id);

            return redirect()->route('admin.customers.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.admin.quotes.view.quote-submitted'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Accept this quote.
     */
    public function acceptQuote(Request $request, $id)
    {
        $adminId = auth()->guard('admin')->user()->id;

        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $quote = $this->customerQuoteRepository->findOrFail($id);

            $quote->update(['status' => 'accepted']);

            $message = $quote->messages()->create([
                'message'    => $request->message,
                'status'     => trans('b2b_suite::app.shop.customers.account.quotes.view.' . $quote->status),
                'user_type'  => 'admin',
                'user_id'    => $adminId,
                'created_at' => now(),
            ]);

            $isAdminLastQuotation = $this->customerQuoteMessageRepository->getLastQuotationMessage($quote->id, 'admin');

            $targetMessageId = $isAdminLastQuotation?->id ?? $message->id;

            if ($targetMessageId) {
                $this->customerQuoteQuotationRepository->updateOrCreate([
                    'message_id' => $targetMessageId,
                    'quote_id'   => $quote->id,
                ], [
                    'is_accepted' => 1,
                    'accepted_by' => 'admin',
                ]);
            }
            
            return redirect()->route('admin.customers.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.admin.quotes.view.quote-accepted'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => trans('b2b_suite::app.admin.quotes.view.error-message')
            ]);
        }
    }

    /**
     * Reject a quote (Open to Rejected) by admin.
     */
    public function rejectQuote(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:1000',
            ]);

            $quote = $this->customerQuoteRepository->findOrFail($id);

            if (in_array($quote->status, [CustomerQuote::STATUS_COMPLETED, CustomerQuote::STATUS_REJECTED])) {
                session()->flash('error', trans('b2b_suite::app.admin.quotes.view.un-authorized-quote'));

                return redirect()->back();
            }

            $quote->status = CustomerQuote::STATUS_REJECTED;

            $quote->save();

            $quote->messages()->create([
                'message'    => $request->message,
                'status'     => trans('b2b_suite::app.admin.quotes.view.'.$quote->status),
                'user_type'  => 'admin',
                'user_id'    => auth()->guard('admin')->user()->id,
                'created_at' => now(),
            ]);

            return redirect()->route('admin.customers.quotes.view', $id)
                ->with('success', trans('b2b_suite::app.admin.quotes.view.quote-rejected'));

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(QuoteRequest $request, int $id)
    {
        Event::dispatch('b2b.quote.update.before', $id);

        $data = $request->only([
            'name',
            'description',
            'company_id',
            'customer_id',
            'agent_id',
            'base_total',
            'total',
            'base_negotiated_total',
            'negotiated_total',
            'expiration_date',
            'status',
        ]);

        $quote = $this->customerQuoteRepository->update($data, $id);

        Event::dispatch('b2b.quote.update.after', $quote);

        session()->flash('success', __('b2b_suite::app.admin.quotes.index.update-success'));

        return redirect()->route('admin.customers.quotes.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $quote = $this->customerQuoteRepository->findOrFail($id);

        try {
            Event::dispatch('b2b.quote.delete.before', $id);

            $this->customerQuoteRepository->delete($id);

            Event::dispatch('b2b.quote.delete.after', $id);

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.quotes.index.delete-success'),
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans('b2b_suite::app.admin.quotes.index.delete-failed'),
        ], 500);
    }

    /**
     * Remove the specified resources from database.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $indices = $massDestroyRequest->input('indices');

        try {
            foreach ($indices as $index) {
                Event::dispatch('b2b.quote.delete.before', $index);

                $this->customerQuoteRepository->delete($index);

                Event::dispatch('b2b.quote.delete.after', $index);
            }

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.quotes.index.index.mass-delete-success'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.quotes.index.delete-failed'),
            ], 500);
        }
    }

    /**
     * Mass update quote.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest)
    {
        try {
            $quoteIds = $massUpdateRequest->input('indices');

            foreach ($quoteIds as $quoteId) {
                Event::dispatch('b2b.quotes.mass-update.before', $quoteId);

                $quote = $this->customerQuoteRepository->find($quoteId);

                $quote->status = $massUpdateRequest->input('value');

                $quote->save();

                Event::dispatch('b2b.quotes.mass-update.after', $quote);
            }

            return new JsonResponse([
                'message' => trans('b2b_suite::app.admin.quotes.index.update-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
