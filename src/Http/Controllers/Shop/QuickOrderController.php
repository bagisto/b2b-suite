<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\Http\Resources\ProductResource;
use Webkul\B2BSuite\Repositories\CompanyRoleRepository;
use Webkul\Customer\Repositories\CustomerGroupRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\Controller;

class QuickOrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerGroupRepository $customerGroupRepository,
        protected CompanyRoleRepository $companyRoleRepository,
        protected ProductRepository $productRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('b2b_suite::shop.customers.account.quick-orders.index');
    }

    /**
     * Method to store user's sign up form data to DB.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            $maxFileSizeMB = core()->getConfigData('b2b.catalog.quick_order.upload_file.max_size') ?? 2;

            $this->validate(request(), [
                'products'            => 'required_without:upload_file|array',
                'products.*.sku'      => 'sometimes|string|distinct|exists:products,sku',
                'products.*.quantity' => 'sometimes|numeric|min:1',
                'upload_file'         => 'required_without:products|file|mimes:csv|max:'.($maxFileSizeMB * 1024),
            ]);

            $data = request()->only('products', 'upload_file');

            if (! empty($data['upload_file'])) {
                $filePath = $data['upload_file']->getRealPath();
                $rows = array_map('str_getcsv', file($filePath));
                $header = array_map('trim', array_shift($rows));
                $products = [];

                foreach ($rows as $row) {
                    $rowData = array_combine($header, $row);
                    $sku = trim($rowData['sku'] ?? '');
                    $quantity = (int) ($rowData['quantity'] ?? 1);

                    if (! empty($sku)) {
                        $products[] = [
                            'sku'      => $sku,
                            'quantity' => $quantity,
                        ];
                    }
                }

                $data['products'] = $products;
            }

            if (empty($data['products'])) {
                return response()->json([
                    'message' => trans('b2b_suite::app.shop.customers.account.quick-orders.no-products-found'),
                ]);
            }

            try {
                b2b_suite()->addProductsToCart($data['products']);

                return response()->json([
                    'status'       => 'success',
                    'message'      => trans('b2b_suite::app.shop.customers.account.quick-orders.create-success'),
                    'redirect_url' => route('shop.customers.account.quick_orders.index'),
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => trans('b2b_suite::app.shop.customers.account.quick-orders.something-went-wrong'),
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'message' => trans('b2b_suite::app.shop.customers.account.quick-orders.no-products-found'),
            ]);
        }
    }

    /**
     * Result of search product.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search()
    {
        $query = trim(request('query'));

        if (empty($query)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $searchEngine = 'database';

        if (
            core()->getConfigData('catalog.products.search.engine') == 'elastic'
            && core()->getConfigData('catalog.products.search.admin_mode') == 'elastic'
        ) {
            $searchEngine = 'elastic';

            $indexNames = core()->getAllChannels()->map(function ($channel) {
                return 'products_'.$channel->code.'_'.app()->getLocale().'_index';
            })->toArray();
        }

        $channelId = $this->customerRepository->find(auth()->guard('customer')->user()->id)->channel_id ?? null;

        $params = [
            'index'      => $indexNames ?? null,
            'name'       => request('query'),
            'sort'       => 'created_at',
            'order'      => 'desc',
            'channel_id' => $channelId,
        ];

        $products = $this->productRepository
            ->setSearchEngine($searchEngine)
            ->getAll($params);

        if ($products->isEmpty()) {
            $params = [
                'index'      => $indexNames ?? null,
                'sku'        => request('query'),
                'sort'       => 'created_at',
                'order'      => 'desc',
                'channel_id' => $channelId,
            ];

            $products = $this->productRepository
                ->setSearchEngine($searchEngine)
                ->getAll($params);
        }

        return ProductResource::collection($products);
    }

    /**
     * Fetch products by skus.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBySkus(Request $request)
    {
        $skus = $request->get('skus', []);

        $products = $this->productRepository->scopeQuery(function ($q) use ($skus) {
            return $q->whereIn('sku', $skus);
        })->get();

        return ProductResource::collection($products);
    }

    /**
     * Download sample file.
     */
    public function downloadSample()
    {
        $samplePath = 'b2b-data/sample/sample_file.csv';

        return Storage::download($samplePath);
    }
}
