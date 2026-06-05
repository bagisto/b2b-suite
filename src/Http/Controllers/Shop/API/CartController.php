<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop\API;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Webkul\B2BSuite\Repositories\CustomerQuoteItemRepository;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shop\Http\Controllers\API\CartController as BaseCartController;
use Webkul\Shop\Http\Resources\CartResource;

class CartController extends BaseCartController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CustomerQuoteItemRepository $customerQuoteItemRepository)
    {
        parent::__construct(app(ProductRepository::class), app(CartRuleCouponRepository::class));

        $this->customerQuoteItemRepository = $customerQuoteItemRepository;
    }

    /**
     * Store items in cart - Override to prevent adding negotiated products when already in cart.
     */
    public function store(): JsonResource
    {
        $this->validate(request(), [
            'product_id' => 'required|integer|exists:products,id',
            'is_buy_now' => 'integer|in:0,1',
            'quantity'   => 'integer|min:1',
        ]);

        $product = $this->productRepository->with('parent')->findOrFail(request()->input('product_id'));

        try {
            if (! $product->status) {
                throw new \Exception(trans('shop::app.checkout.cart.inactive-add'));
            }

            $requestedQty = request()->input('quantity', 1);
            $cart = Cart::getCart();

            if ($cart && $cart->items->count() > 0) {
                $existingCartItem = $cart->items->where('product_id', $product->id)->first();

                if ($existingCartItem) {
                    $existingAdditional = $existingCartItem->additional ?? [];

                    if (isset($existingAdditional['quote_id'])) {
                        return new JsonResource([
                            'message' => trans('b2b_suite::app.shop.checkout.cart.cannot-add-product-with-negotiated-price'),
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            $response = [];

            if (request()->get('is_buy_now')) {
                Cart::deActivateCart();

                $response['redirect'] = route('shop.checkout.onepage.index');
            }

            $cart = Cart::addProduct($product, request()->all());

            return new JsonResource(array_merge([
                'data'    => new CartResource($cart),
                'message' => trans('shop::app.checkout.cart.item-add-to-cart'),
            ], $response));
        } catch (\Exception $exception) {
            return response()->json([
                'redirect_uri' => route('shop.product_or_category.index', $product->url_key),
                'message'      => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Updates the quantity of the items present in the cart.
     */
    public function update(): JsonResource
    {
        try {
            $data = request()->input();

            $cart = Cart::getCart();

            foreach ($data['qty'] as $cartItemId => $quantity) {
                $cartItem = $cart->items->where('id', $cartItemId)->first();

                if (! $cartItem) {
                    continue;
                }

                $additional = $cartItem->additional;

                if (isset($additional['quote_id']) && isset($additional['quote_item_id'])) {
                    $negotiation = $this->customerQuoteItemRepository
                        ->where('id', $additional['quote_item_id'])
                        ->where('customer_quote_id', $additional['quote_id'])
                        ->first();

                    if ($negotiation && $quantity != $negotiation->negotiated_qty) {
                        return new JsonResource([
                            'message' => trans('b2b_suite::app.shop.checkout.cart.cannot-change-negotiated-quantity'),
                        ]);
                    }
                }
            }

            Cart::updateItems($data);

            return new JsonResource([
                'data'    => new CartResource(Cart::getCart()),
                'message' => trans('shop::app.checkout.cart.index.quantity-update'),
            ]);

        } catch (\Exception $exception) {
            return new JsonResource([
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
