<?php

namespace Webkul\B2BSuite\Listeners;

use Webkul\B2BSuite\Models\CustomerQuote;
use Webkul\Sales\Contracts\Invoice as InvoiceContract;
use Webkul\Sales\Contracts\Order as OrderContract;
use Webkul\Sales\Contracts\Shipment as ShipmentContract;
use Webkul\Sales\Models\Order as SalesOrder;
use Webkul\Shop\Listeners\Base;

class Order extends Base
{
    /**
     * After order is created.
     *
     * @return void
     */
    public function afterCreated(OrderContract $order)
    {
        if (! (bool) core()->getConfigData('b2b_suite.general.settings.active')) {
            return;
        }

        $quoteStatus = CustomerQuote::STATUS_ORDERED;

        $isQuote = false;
        foreach ($order->items as $orderItem) {
            if (
                isset($orderItem->additional['quote_id'])
                && isset($orderItem->additional['quote_item_id'])
            ) {
                $quote = $this->updateQuoteStatus($orderItem->additional, $quoteStatus);

                if (isset($quote->id)) {
                    $isQuote = $quote;
                }
            }
        }

        if ($isQuote) {
            $isQuote->state = CustomerQuote::STATE_PURCHASE_ORDER;
            $isQuote->status = $quoteStatus;
            $isQuote->order_id = $order->id;
            $isQuote->save();
        }
    }

    /**
     * After invoice/shipment is created.
     *
     * @return void
     */
    public function afterUpdated(InvoiceContract|ShipmentContract $invoiceOrShipment)
    {
        $order = app('Webkul\Sales\Repositories\OrderRepository')->find($invoiceOrShipment->order->id);

        if (
            ! (bool) core()->getConfigData('b2b_suite.general.settings.active')
            || $order->status != SalesOrder::STATUS_COMPLETED
        ) {
            return;
        }

        $quoteStatus = CustomerQuote::STATUS_COMPLETED;

        $customerQuoteRepository = app('Webkul\B2BSuite\Repositories\CustomerQuoteRepository');
        $quote = $customerQuoteRepository->findOneByField('order_id', $order->id);

        if (! $quote || $quote->status == $quoteStatus) {
            return;
        }

        $isQuote = false;
        foreach ($order->items as $orderItem) {
            if (
                isset($orderItem->additional['quote_id'])
                && isset($orderItem->additional['quote_item_id'])
            ) {
                $quote = $this->updateQuoteStatus($orderItem->additional, $quoteStatus);

                if (isset($quote->id)) {
                    $isQuote = $quote;
                }
            }
        }

        if ($isQuote) {
            $isQuote->state = CustomerQuote::STATE_PURCHASE_ORDER;
            $isQuote->status = $quoteStatus;
            $isQuote->save();
        }
    }

    /**
     * Update quote status
     */
    public function updateQuoteStatus($quoteData, $quoteStatus): ?CustomerQuote
    {
        $customerQuoteRepository = app('Webkul\B2BSuite\Repositories\CustomerQuoteRepository');

        $quote = $customerQuoteRepository->find($quoteData['quote_id']);

        if (! $quote) {
            return null;
        }

        $quoteItem = $quote->items->where('id', $quoteData['quote_item_id'])->first();

        if (! $quoteItem) {
            return null;
        }

        $quoteItem->status = $quoteStatus;
        $quoteItem->save();

        return $quote;
    }
}
