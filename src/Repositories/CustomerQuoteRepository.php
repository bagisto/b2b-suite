<?php

namespace Webkul\B2BSuite\Repositories;

use Illuminate\Container\Container;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Webkul\B2BSuite\Contracts\CustomerQuote;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Core\Eloquent\Repository;
use Webkul\Product\Repositories\ProductRepository;

class CustomerQuoteRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected ProductRepository $productRepository,
        protected CustomerQuoteItemRepository $customerQuoteItemRepository,
        protected CustomerQuoteQuotationRepository $customerQuoteQuotationRepository,
        protected CustomerQuoteAttachmentRepository $customerQuoteAttachmentRepository,
        protected CustomerQuoteMessageRepository $customerQuoteMessageRepository,
        protected Container $container,
    ) {
        parent::__construct($container);
    }

    /**
     * Specify Model class name.
     */
    public function model()
    {
        return CustomerQuote::class;
    }

    /**
     * Create a quote.
     */
    public function create(array $data): CustomerQuote
    {
        $cart = $data['cart'] ?? Cart::getCart();

        $data['expiration_date'] = $data['expiration_date'] ?? $this->calculateExpirationDate();

        $data = array_merge($data, $this->prepareCartData($cart));

        $quote = $this->model->create($data);

        $quoteItemDetails = $this->prepareCartData($cart, $quote);

        if (! empty($quoteItemDetails['items'])) {
            foreach ($quoteItemDetails['items'] as $item) {
                $this->customerQuoteItemRepository->create($item);
            }
        }

        $this->customerQuoteMessageRepository->create([
            'quote_id'  => $quote->id,
            'user_id'   => $data['customer_id'],
            'user_type' => isset($data['customer_id']) ? 'customer' : 'admin',
            'message'   => $data['message'] ?? trans('b2b_suite::app.shop.checkout.cart.request-quote.default-message', [
                'status'  => $quote->status,
            ]),
        ]);

        $this->upload($data, $quote, 'attachments');

        Cart::removeCart($cart);

        return $quote;
    }

    /**
     * Prepare cart data for storage.
     */
    private function prepareCartData($cart, $quote = null): array
    {
        if ($quote) {
            return [
                'items' => $cart->items->map(function ($item) use ($quote) {
                    return [
                        'customer_quote_id'     => $quote->id,
                        'product_id'            => $item->product_id,
                        'type'                  => $item->product->type,
                        'sku'                   => $item->product->sku,
                        'name'                  => $item->product->name,
                        'qty'                   => $item->quantity,
                        'negotiated_qty'        => $item->quantity,
                        'price'                 => $item->price,
                        'base_price'            => $item->base_price,
                        'total'                 => $item->total,
                        'base_total'            => $item->base_total,
                        'negotiated_price'      => $item->price,
                        'base_negotiated_price' => $item->base_price,
                        'negotiated_total'      => $item->total,
                        'base_negotiated_total' => $item->base_total,
                        'note'                  => '',
                        'status'                => $quote->status,
                        'additional'            => json_encode($item->additional),
                    ];
                })->toArray(),
            ];
        }

        return [
            'cart_id'               => $cart->id,
            'total'                 => $cart->grand_total,
            'base_total'            => $cart->base_grand_total,
            'negotiated_total'      => $cart->grand_total,
            'base_negotiated_total' => $cart->base_grand_total,
        ];
    }

    /**
     * Calculate expiration date based on configuration.
     */
    private function calculateExpirationDate(): \Carbon\Carbon
    {
        $period = (int) core()->getConfigData('b2b_suite.quotes.settings.default_expiration_period', 30);
        $unit = core()->getConfigData('b2b_suite.quotes.settings.expiration_period_unit', 'days');

        return match ($unit) {
            'weeks'  => now()->addWeeks($period),
            'months' => now()->addMonths($period),
            default  => now()->addDays($period),
        };
    }

    /**
     * Upload.
     *
     * @param  array  $data
     * @param  \Webkul\B2BSuite\Contracts\CustomerQuote  $quote
     */
    public function upload($data, $quote, string $uploadFileType): void
    {
        /**
         * Previous model ids for filtering.
         */
        $previousIds = $quote->attachments()->pluck('id');

        if (! empty($data[$uploadFileType])) {
            foreach ($data[$uploadFileType] as $indexOrModelId => $file) {
                if ($file instanceof UploadedFile) {
                    if (Str::contains($file->getMimeType(), 'image')) {
                        $manager = new ImageManager;

                        $image = $manager->make($file)->encode('webp');

                        $path = 'b2b-quote/'.$quote->id.'/'.Str::random(40).'.webp';

                        Storage::put($path, $image);
                    } else {
                        $path = $file->store('b2b-quote/'.$quote->id);
                    }

                    $this->customerQuoteAttachmentRepository->create([
                        'customer_quote_id' => $quote->id,
                        'name'              => $file->getClientOriginalName(),
                        'path'              => $path,
                        'mime_type'         => $file->getClientMimeType(),
                        'size'              => $file->getSize(),
                    ]);
                } else {
                    if (is_numeric($index = $previousIds->search($indexOrModelId))) {
                        $previousIds->forget($index);
                    }
                }
            }
        }

        foreach ($previousIds as $indexOrModelId) {
            if (! $model = $this->customerQuoteAttachmentRepository->find($indexOrModelId)) {
                continue;
            }

            Storage::delete($model->path);

            $this->customerQuoteAttachmentRepository->delete($indexOrModelId);
        }
    }

    /**
     * Update cart.
     *
     * @param  array  $data
     * @return \Webkul\B2BSuite\Contracts\CustomerQuote $quote
     */
    public function updateCart($id)
    {
        $quote = $this->find($id);

        $cart = Cart::getCart();

        if (! $cart) {
            $cart = $this->cartRepository->create([
                'company_id'            => $quote?->customer?->company_id ?? null,
                'is_guest'              => 1,
                'channel_id'            => core()->getCurrentChannel()->id,
                'global_currency_code'  => $baseCurrencyCode = core()->getBaseCurrencyCode(),
                'base_currency_code'    => $baseCurrencyCode,
                'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
                'cart_currency_code'    => core()->getCurrentCurrencyCode(),
            ]);
        }

        foreach ($quote->items as $quoteItem) {
            $product = $this->productRepository->find($quoteItem->product_id);

            $additional = $quoteItem->additional ? json_decode($quoteItem->additional, true) : [];

            $data = array_merge($additional, [
                'product_id'    => $product->id,
                'quantity'      => $quoteItem->negotiated_qty,
                'quote_id'      => $quote->id,
                'quote_item_id' => $quoteItem->id,
            ]);

            $cart = Cart::addProduct($product, $data);

            $cartItems = $cart->items->where('product_id', $product->id);

            foreach ($cartItems as $cartItem) {
                $additional = $cartItem->additional;

                if (
                    (isset($additional['quote_id']) && $additional['quote_id'] == $quote->id)
                    && (isset($additional['quote_item_id']) && $additional['quote_item_id'] == $quoteItem->id)
                ) {
                    $cartItem->custom_price = core()->convertPrice($quoteItem->base_negotiated_price);
                    $cartItem->save();
                }
            }

            Cart::collectTotals();
        }
    }

    /**
     * Generate quotation number and purchase order number.
     *
     * @param  int|null  $quoteId
     */
    public function generateQuotationNumber($quoteId): array
    {
        $maxId = $quoteId ?? $this->model->max('id') + 1;

        $quotePrefix = core()->getConfigData('b2b_suite.quotes.settings.quote_prefix');
        $poPrefix = core()->getConfigData('b2b_suite.quotes.settings.po_prefix');
        $defaultPadding = core()->getConfigData('b2b_suite.quotes.settings.default_padding');

        $defaultNumber = Str::padLeft($maxId, $defaultPadding ?? 10, '0');

        if (! empty($quotePrefix)) {
            $quotationNumber = $quotePrefix.$defaultNumber;
        }

        if (! empty($poPrefix)) {
            $poNumber = $poPrefix.$defaultNumber;
        }

        return [
            'quotation_number' => $quotationNumber ?? $defaultNumber,
            'po_number'        => $poNumber ?? $defaultNumber,
        ];
    }

    /**
     * Create/Update message quotation.
     *
     * @param  int  $id
     * @return \Webkul\B2BSuite\Contracts\CustomerQuote $quote
     */
    public function createOrUpdateMessageQuotation(array $data, $id)
    {
        $quote = $this->find($id);

        $itemNegotiatedTotal = 0;

        foreach ($data['items'] ?? [] as $itemId => $itemData) {
            if (! $item = $this->customerQuoteItemRepository->find($itemId)) {
                continue;
            }

            $negotiatedTotal = $itemData['negotiated_price'] * $itemData['negotiated_qty'];

            $itemNegotiatedTotal += $negotiatedTotal;

            $this->customerQuoteItemRepository->update([
                'negotiated_qty'        => $itemData['negotiated_qty'],
                'negotiated_price'      => $itemData['negotiated_price'],
                'base_negotiated_price' => core()->convertToBasePrice($itemData['negotiated_price'], core()->getBaseCurrencyCode()),
                'negotiated_total'      => $negotiatedTotal,
                'base_negotiated_total' => core()->convertToBasePrice($negotiatedTotal, core()->getBaseCurrencyCode()),
                'note'                  => $data['message'] ?? trans('b2b_suite::app.shop.customers.account.quotes.view.item-updated', [
                    'name'  => $quote->customer->name,
                ]),
                'status'                => $data['status'] ?? $quote->status,
            ], $itemId);

            $this->customerQuoteQuotationRepository->updateOrCreate([
                'message_id'    => $data['message_id'] ?? null,
                'quote_id'      => $quote->id,
                'quote_item_id' => $item->id,
            ], [
                'sku'        => $item->sku,
                'name'       => $item->name,
                'qty'        => $itemData['negotiated_qty'],
                'price'      => $itemData['negotiated_price'],
                'base_price' => core()->convertToBasePrice($itemData['negotiated_price'], core()->getBaseCurrencyCode()),
                'total'      => $negotiatedTotal,
                'base_total' => core()->convertToBasePrice($negotiatedTotal, core()->getBaseCurrencyCode()),
            ]);
        }

        $quote->update([
            'status'                => $data['status'] ?? $quote->status,
            'negotiated_total'      => $itemNegotiatedTotal,
            'base_negotiated_total' => core()->convertToBasePrice($itemNegotiatedTotal, core()->getBaseCurrencyCode()),
        ]);

        return $quote;
    }
}
