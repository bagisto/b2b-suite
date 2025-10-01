<?php

namespace Webkul\B2BSuite\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Facades\ProductImage;

class RequisitionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $product = $this->product;

        if ($this->variant_id) {
            $product = $this->variant;
        }

        return [
            'id'                  => $this->id,
            'requisition_list_id' => $this->requisition_list_id,
            'product_id'          => $this->product_id,
            'variant_id'          => $this->variant_id,
            'type'                => $this->type,
            'sku'                 => $this->sku,
            'name'                => $this->name,
            'quantity'            => $this->qty,
            'price'               => $this->price,
            'formatted_price'     => core()->formatPrice($this->price),
            'total'               => $this->total,
            'formatted_total'     => core()->formatPrice($this->total),
            'base_image'          => ProductImage::getProductBaseImage($product),
            'product_url_key'     => $this->product->url_key,
            'options'             => $this->formatAdditionalAttributes(),
        ];
    }

    /**
     * Format the additional attributes.
     */
    public function formatAdditionalAttributes(): array
    {
        $this->resource->additional = $this->resource->additional ? json_decode($this->resource->additional, true) : [];

        $attributes = $this->resource->additional['attributes'] ?? [];

        if (! empty($attributes)) {
            return collect($attributes)
                ->map(function ($attribute) {
                    if (
                        isset($attribute['attribute_type'])
                        && $attribute['attribute_type'] == 'file'
                    ) {
                        $attribute['file_name'] = File::basename($attribute['option_label']);

                        $attribute['file_url'] = Storage::url($attribute['option_label']);
                    }

                    return $attribute;
                })
                ->toArray();
        }

        return [];
    }
}
