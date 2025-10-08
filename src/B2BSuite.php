<?php

namespace Webkul\B2BSuite;

use Illuminate\Support\Facades\Event;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

class B2BSuite
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository,
    ) {
    }

    /**
     * Process and add products to cart
     *
     * @return bool
     */
    public function addProductsToCart($data)
    {
        foreach ($data as $item) {
            $product = $this->productRepository->with('parents', 'variants')->findOneByField('sku', $item['sku']);

            if (! $product) {
                continue;
            }

            $cartData = $this->prepareCartData($product, $item);

            if (isset($cartData['product_id'])) {
                $cart = Cart::addProduct($product, $cartData);
                continue;
            }

            // Cart::addProduct($product, $item['quantity']);
        }
    }

    public function prepareCartData($product, $item)
    {
        switch ($product->type) {
            case 'simple':
                $buyRequest = [
                    'product_id' => $product->id,
                    'is_buy_now' => 0,
                    'quantity'   => $item['quantity'] ?? 1,
                ];
                break;

            case 'configurable':
                $buyRequest = [
                    'quantity' => $item['quantity'],
                    'super_attribute' => $this->getSuperAttributes($product),
                ];
                break;

            case 'bundle':
                $buyRequest = [
                    'quantity' => $item['quantity'],
                    'bundle_options' => $this->getBundleOptions($product),
                ];
                break;

            case 'downloadable':
                $buyRequest = [
                    'quantity' => $item['quantity'],
                    'downloadable_links' => $this->getDownloadableLinks($product),
                ];
                break;

            default:
                $buyRequest = [
                    'quantity' => $item['quantity'],
                ];
                break;
        }

        return $buyRequest;
    }
}
