<?php

namespace Webkul\B2BSuite\Http\Controllers\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Resources\CartResource;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;

class CartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected CustomerRepository $customerRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Cart.
     */
    public function index(int $id): JsonResource
    {
        $cart = $this->cartRepository->findOrFail($id);

        $response = [
            'data' => new CartResource($cart),
        ];

        if (session()->has('info')) {
            $response['message'] = session()->get('info');
        }

        return new JsonResource($response);
    }

    /**
     * Create cart
     */
    public function store(): JsonResource
    {
        if (request()->input('cart_id')) {
            $cart = $this->cartRepository->find(request()->input('cart_id'));

            $cart->company_id = request()->input('company_id');

            $cart->save();

            return new JsonResource([
                'data'         => new CartResource($cart),
                'redirect_url' => route('admin.customers.quotes.create', $cart->id),
            ]);
        }

        $customer = $this->customerRepository->findOrFail(request()->input('customer_id'));

        try {
            $cart = Cart::createCart([
                'customer'  => $customer,
                'is_active' => false,
            ]);

            return new JsonResource([
                'data'         => new CartResource($cart),
                'redirect_url' => route('admin.customers.quotes.create', $cart->id),
            ]);
        } catch (\Exception $exception) {
            return new JsonResource([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Store items in cart.
     */
    public function storeItem(int $cartId): JsonResource
    {
        $this->validate(request(), [
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $cart = $this->cartRepository->findOrFail($cartId);

        Cart::setCart($cart);

        try {
            $params = request()->all();

            $product = $this->productRepository->findOrFail($params['product_id']);

            Cart::addProduct($product, $params);

            return new JsonResource([
                'data'    => new CartResource(Cart::getCart()),
                'message' => trans('admin::app.sales.orders.create.cart.success-add-to-cart'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResource([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Removes the item from the cart if it exists.
     */
    public function destroyItem(int $cartId): JsonResource
    {
        $this->validate(request(), [
            'cart_item_id' => 'required|exists:cart_items,id',
        ]);

        $cart = $this->cartRepository->findOrFail($cartId);

        Cart::setCart($cart);

        Cart::removeItem(request()->input('cart_item_id'));

        Cart::collectTotals();

        return new JsonResource([
            'data'    => new CartResource(Cart::getCart()),
            'message' => trans('admin::app.sales.orders.create.cart.success-remove'),
        ]);
    }

    /**
     * Updates the quantity of the items present in the cart.
     */
    public function updateItem(int $cartId): JsonResource
    {
        $cart = $this->cartRepository->findOrFail($cartId);

        Cart::setCart($cart);

        try {
            Cart::updateItems(request()->input());

            return new JsonResource([
                'data'    => new CartResource(Cart::getCart()),
                'message' => trans('admin::app.sales.orders.create.cart.success-update'),
            ]);
        } catch (\Exception $exception) {
            return new JsonResource([
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
