<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\B2BSuite\DataGrids\Admin\CompanyCreditDataGrid;
use Webkul\B2BSuite\DataGrids\Admin\CompanyCreditTransactionDataGrid;
use Webkul\B2BSuite\Helpers\CreditManager;
use Webkul\B2BSuite\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;

class CompanyCreditController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CreditManager $creditManager,
        protected CustomerRepository $customerRepository,
    ) {}

    /**
     * Listing of all company credit accounts (accounts-receivable overview).
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(CompanyCreditDataGrid::class)->process();
        }

        return view('b2b::admin.companies.credit.listing');
    }

    /**
     * Show a company's credit account, ledger and management forms. The history datagrid
     * fetches its rows from this same route over ajax (scoped to the company's ledger).
     */
    public function show($id)
    {
        $company = $this->customerRepository->findOrFail($id);

        abort_unless(Customer::repCanAccessCompany((int) $id), 403);

        $credit = $this->creditManager->getOrCreate($company->id);

        if (request()->ajax()) {
            $datagrid = app(CompanyCreditTransactionDataGrid::class);

            $datagrid->companyCreditId = $credit->id;

            return $datagrid->process();
        }

        $companyFlat = DB::table('company_flat')
            ->where('customer_id', $company->id)
            ->where('locale', app()->getLocale())
            ->first();

        return view('b2b::admin.companies.credit.index', compact('company', 'credit', 'companyFlat'));
    }

    /**
     * Set / update the company's credit limit and flags.
     */
    public function updateLimit($id)
    {
        $company = $this->customerRepository->findOrFail($id);

        abort_unless(Customer::repCanAccessCompany((int) $id), 403);

        $data = request()->validate([
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'allow_exceed_limit' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'boolean'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);

        $credit = $this->creditManager->getOrCreate($company->id);

        $this->creditManager->setLimit($credit, [
            'credit_limit' => $data['credit_limit'],
            'allow_exceed_limit' => (bool) ($data['allow_exceed_limit'] ?? false),
            'status' => (bool) ($data['status'] ?? true),
            'comment' => $data['comment'] ?? null,
        ], $this->actor());

        session()->flash('success', trans('b2b::app.admin.companies.credit.limit-updated'));

        return redirect()->route('admin.b2b.companies.credit', $company->id);
    }

    /**
     * Record an offline payment received against the company's balance.
     */
    public function reimburse($id)
    {
        $company = $this->customerRepository->findOrFail($id);

        abort_unless(Customer::repCanAccessCompany((int) $id), 403);

        $credit = $this->creditManager->getOrCreate($company->id);

        /**
         * No upper bound: paying more than the outstanding balance is allowed and the
         * surplus is held as advance credit (the balance goes negative).
         */
        $data = request()->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);

        $this->creditManager->reimburse($credit, (float) $data['amount'], [
            'reference' => $data['reference'] ?? null,
            'comment' => $data['comment'] ?? null,
        ], $this->actor());

        session()->flash('success', trans('b2b::app.admin.companies.credit.reimbursed'));

        return redirect()->route('admin.b2b.companies.credit', $company->id);
    }

    /**
     * The acting admin, for the ledger audit trail.
     */
    protected function actor(): array
    {
        return [
            'type' => 'admin',
            'id' => auth()->guard('admin')->id(),
        ];
    }
}
