<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\B2BSuite\DataGrids\Admin\CompanyCatalogDataGrid;
use Webkul\B2BSuite\Helpers\CompanyCatalog as CompanyCatalogHelper;
use Webkul\B2BSuite\Repositories\CompanyCatalogRepository;
use Webkul\Product\Repositories\ProductRepository;

class CompanyCatalogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CompanyCatalogRepository $companyCatalogRepository,
        protected CompanyCatalogHelper $companyCatalogHelper,
    ) {}

    /**
     * Display a listing of the company catalogs.
     */
    public function index()
    {
        if (request()->ajax()) {
            return app(CompanyCatalogDataGrid::class)->process();
        }

        return view('b2b::admin.company-catalogs.index');
    }

    /**
     * Show the form for creating a new company catalog.
     */
    public function create()
    {
        return view('b2b::admin.company-catalogs.create');
    }

    /**
     * Store a newly created company catalog.
     */
    public function store()
    {
        $this->validateRequest();

        $catalog = $this->companyCatalogRepository->create([
            'name' => request('name'),
            'description' => request('description'),
            'status' => request('status') !== null ? (int) (bool) request('status') : 1,
        ]);

        $this->persistRelations($catalog);

        session()->flash('success', trans('b2b::app.admin.company-catalogs.create-success'));

        return to_route('admin.b2b.company_catalogs.index');
    }

    /**
     * Show the form for editing the specified company catalog.
     */
    public function edit($id)
    {
        $catalog = $this->companyCatalogRepository->findOrFail($id);

        $products = $this->prepareProducts($catalog);

        $companyIds = $catalog->companies()->pluck('id')->toArray();

        $flats = DB::table('company_flat')
            ->whereIn('customer_id', $companyIds)
            ->get()
            ->keyBy('customer_id');

        $companies = collect($companyIds)->map(function ($id) use ($flats) {
            $flat = $flats->get($id);

            return [
                'id' => $id,
                'name' => ($flat->business_name ?? null) ?: ($flat->email ?? '#'.$id),
                'email' => $flat->email ?? '',
            ];
        })->values();

        return view('b2b::admin.company-catalogs.edit', compact('catalog', 'products', 'companies'));
    }

    /**
     * Search companies by company (business) name for the picker.
     */
    public function companies()
    {
        $query = trim((string) request('query'));

        $companies = DB::table('company_flat')
            ->leftJoin('customers', 'company_flat.customer_id', '=', 'customers.id')
            ->leftJoin('company_catalogs', 'customers.company_catalog_id', '=', 'company_catalogs.id')
            ->where('customers.type', 'company')
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($sub) use ($query) {
                    $sub->where('company_flat.business_name', 'like', '%'.$query.'%')
                        ->orWhere('company_flat.email', 'like', '%'.$query.'%');
                });
            })
            ->select(
                'company_flat.customer_id as id',
                'company_flat.business_name',
                'company_flat.email',
                'company_catalogs.name as current_catalog'
            )
            ->groupBy('company_flat.customer_id', 'company_flat.business_name', 'company_flat.email', 'company_catalogs.name')
            ->orderBy('company_flat.business_name')
            ->limit(10)
            ->get()
            ->map(fn ($company) => [
                'id' => $company->id,
                'name' => $company->business_name ?: $company->email,
                'email' => $company->email,
                'current_catalog' => $company->current_catalog,
            ]);

        return response()->json(['data' => $companies]);
    }

    /**
     * Paginated, searchable product list for the "Assign Products" modal.
     */
    public function products()
    {
        $query = trim((string) request('query'));

        $products = app(ProductRepository::class)
            ->setSearchEngine('database')
            ->getAll(array_filter([
                'query' => $query !== '' ? $query : null,
                'sort' => 'created_at',
                'order' => 'desc',
                'limit' => 10,
            ]));

        return response()->json([
            'data' => collect($products->items())->map(fn ($product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'formatted_price' => core()->formatPrice($product->price),
                'image' => $product->images->first()?->url,
            ])->values(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Update the specified company catalog.
     */
    public function update($id)
    {
        $this->validateRequest();

        $catalog = $this->companyCatalogRepository->findOrFail($id);

        $this->companyCatalogRepository->update([
            'name' => request('name'),
            'description' => request('description'),
            'status' => request('status') !== null ? (int) (bool) request('status') : 1,
        ], $id);

        $this->persistRelations($catalog->refresh());

        session()->flash('success', trans('b2b::app.admin.company-catalogs.update-success'));

        return to_route('admin.b2b.company_catalogs.index');
    }

    /**
     * Remove the specified company catalog.
     */
    public function destroy($id)
    {
        $catalog = $this->companyCatalogRepository->findOrFail($id);

        try {
            $this->companyCatalogHelper->cleanup($catalog);

            $this->companyCatalogRepository->delete($id);

            return response()->json([
                'message' => trans('b2b::app.admin.company-catalogs.delete-success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('b2b::app.admin.company-catalogs.delete-failed'),
            ], 500);
        }
    }

    /**
     * Validate the catalog request.
     */
    protected function validateRequest(): void
    {
        $this->validate(request(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|nullable|boolean',
            'products' => 'array',
            'products.*' => 'integer',
            'prices' => 'array',
            'prices.*.type' => 'nullable|in:fixed,discount',
            'prices.*.value' => 'nullable|numeric|min:0',
            'companies' => 'array',
            'companies.*' => 'integer',
        ]);
    }

    /**
     * Persist products, prices and company assignments for a catalog.
     */
    protected function persistRelations($catalog): void
    {
        $this->companyCatalogHelper->provisionGroup($catalog);

        $catalog = $this->companyCatalogRepository->find($catalog->id);

        $this->companyCatalogHelper->syncProducts($catalog, request('products', []));

        $this->companyCatalogHelper->setPrices($catalog, request('prices', []));

        $this->companyCatalogHelper->assignCompanies($catalog, request('companies', []));
    }

    /**
     * Build the product rows (with catalog prices) for the edit screen.
     */
    protected function prepareProducts($catalog): Collection
    {
        $priceRows = collect();

        if ($catalog->customer_group_id) {
            $priceRows = DB::table('product_customer_group_prices')
                ->where('customer_group_id', $catalog->customer_group_id)
                ->get()
                ->keyBy('product_id');
        }

        return $catalog->products()->with('images')->get()->map(function ($product) use ($priceRows) {
            $row = $priceRows->get($product->id);

            return [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'price' => (float) $product->price,
                'formatted_price' => core()->formatPrice($product->price),
                'image' => $product->images->first()?->url,
                'price_type' => $row->value_type ?? 'fixed',
                'price_value' => $row ? (float) $row->value : '',
            ];
        });
    }
}
