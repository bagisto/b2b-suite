<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop\API;

use Webkul\Shop\Http\Controllers\API\CartController as BaseCartController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Webkul\CartRule\Repositories\CartRuleCouponRepository;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Shipping\Facades\Shipping;
use Webkul\Shop\Http\Resources\CartResource;
use Webkul\Shop\Http\Resources\ProductResource;
use Webkul\B2BSuite\Repositories\CustomerQuoteItemRepository;


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

                $negotiation = $this->customerQuoteItemRepository
                    ->join('customer_quotes', 'customer_quotes.id', '=', 'customer_quote_items.customer_quote_id')
                    ->where('customer_quote_items.product_id', $cartItem->product_id)
                    ->where('customer_quotes.status', 'accepted')
                    ->select('customer_quote_items.*')
                    ->first();

                if ($negotiation) {
                    if ($quantity != $negotiation->negotiated_qty) {

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