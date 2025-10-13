<?php

namespace Webkul\B2BSuite;

use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

class B2BSuite
{
    /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Process and add products to cart
     *
     * @return bool
     */
    public function addProductsToCart($data)
    {
        foreach ($data as $item) {

            $product = $this->productRepository->with([
                'parent',
                'variants',
                'bundle_options.bundle_option_products',
                'grouped_products.associated_product',
            ])->findOneByField('sku', $item['sku']);

            $cartData = $this->prepareCartData($product, $item);

            try {
                Cart::addProduct($product, $cartData);
            } catch (\Exception $e) {
            }
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
                $variant = $this->getVariant($product);

                $superAttributes = $this->getSuperAttributesForVariant($product, $variant);

                $buyRequest = [
                    'product_id'                   => $variant->id,
                    'quantity'                     => $item['quantity'] ?? 1,
                    'super_attribute'              => $superAttributes,
                    'selected_configurable_option' => $variant->id,
                ];

                break;

            case 'bundle':
                $buyRequest = [
                    'quantity'       => $item['quantity'],
                    'bundle_options' => $this->getBundleOptions($product),
                ];
                break;

            case 'grouped':
                $buyRequest = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'] ?? 1,
                    'qty'        => $this->getGroupedProductQuantities($product, $item),
                ];

                break;

            case 'downloadable':
                $buyRequest = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'links'      => $this->getDownloadableLinks($product),
                ];
                break;

            default:
                $buyRequest = [
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                ];
                break;
        }

        return $buyRequest;
    }

    /**
     * Get IDs of all downloadable links for the product
     */
    public function getDownloadableLinks($product)
    {
        $links = [];

        if (isset($product->downloadable_links) && $product->downloadable_links->isNotEmpty()) {
            foreach ($product->downloadable_links as $link) {
                $links[] = $link->id;
            }
        }

        return $links;
    }

    /**
     * Get the first available variant of a configurable product
     */
    public function getVariant($product)
    {
        if (! $product->variants || $product->variants->isEmpty()) {
            return null;
        }

        foreach ($product->variants as $variant) {
            if ($variant->status && (! $variant->manage_stock || $variant->quantity > 0)) {
                return $variant;
            }
        }

        $firstVariant = $product->variants->first();

        return $firstVariant;
    }

    /**
     * Get super attributes for a specific variant
     */
    public function getSuperAttributesForVariant($product, $variant)
    {
        $superAttributes = [];

        if (! $product->super_attributes || $product->super_attributes->isEmpty()) {
            return $superAttributes;
        }

        foreach ($product->super_attributes as $attribute) {
            $attributeValue = $variant->{$attribute->code};

            if ($attributeValue) {
                $superAttributes[$attribute->id] = $attributeValue;
            }
        }

        return $superAttributes;
    }

    /**
     * Get bundle options with default selections
     */
    public function getBundleOptions($product)
    {
        $bundleOptions = [];

        if (! $product->bundle_options || $product->bundle_options->isEmpty()) {
            return $bundleOptions;
        }

        foreach ($product->bundle_options as $bundleOption) {
            $optionValues = [];

            if ($bundleOption->bundle_option_products) {
                foreach ($bundleOption->bundle_option_products as $bundleOptionProduct) {
                    $optionValues[] = $bundleOptionProduct->id;
                }
            }

            if (! empty($optionValues)) {
                $bundleOptions[$bundleOption->id] = [$optionValues[0]];
            }
        }

        return $bundleOptions;
    }

    /**
     * Get super attributes with
     */
    public function getSuperAttributes($product)
    {
        $superAttributes = [];

        if (! $product->super_attributes || $product->super_attributes->isEmpty()) {
            return $superAttributes;
        }

        foreach ($product->super_attributes as $attribute) {
            if ($attribute->options && $attribute->options->isNotEmpty()) {
                $superAttributes[$attribute->id] = $attribute->options->first()->id;
            }
        }

        return $superAttributes;
    }

    /**
     * Get quantities for grouped product's associated products
     */
    public function getGroupedProductQuantities($product, $item)
    {
        $quantities = [];

        if (! $product->relationLoaded('grouped_products')) {
            $product->load('grouped_products.associated_product');
        }

        if (! $product->grouped_products || $product->grouped_products->isEmpty()) {
            return $quantities;
        }

        $requestedQty = $item['quantity'] ?? 1;

        foreach ($product->grouped_products as $groupedProduct) {
            if ($groupedProduct->associated_product && $groupedProduct->associated_product->status) {
                $quantities[$groupedProduct->associated_product_id] = $requestedQty;
            }
        }

        return $quantities;
    }
}
