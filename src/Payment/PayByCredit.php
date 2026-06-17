<?php

namespace Webkul\B2BSuite\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\B2BSuite\Helpers\CreditManager;
use Webkul\Payment\Payment\Payment;

class PayByCredit extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'paybycredit';

    /**
     * Get redirect url (offline method — none).
     */
    public function getRedirectUrl() {}

    /**
     * Shown whenever the method + the credit feature are on and the cart belongs to a
     * company with an active credit account. Affordability is NOT gated here so the buyer
     * still sees the method when the total exceeds their credit — the reason is surfaced in
     * the description (see getDescription()).
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->companyCredit() !== null;
    }

    /**
     * Description shown under the method: the configured copy when the company can cover
     * the cart, or an "insufficient credit" notice (with the available amount) when it
     * cannot.
     *
     * @return string
     */
    public function getDescription()
    {
        $credit = $this->companyCredit();

        if ($credit) {
            $creditManager = app(CreditManager::class);

            if (! $creditManager->canAfford($credit, (float) $this->cart->base_grand_total)) {
                return trans('b2b::app.shop.checkout.pay-by-credit.insufficient', [
                    'available' => core()->formatBasePrice($creditManager->availableCredit($credit)),
                ]);
            }
        }

        return $this->getConfigData('description');
    }

    /**
     * The active company credit account backing the current cart, or null when the method
     * does not apply (feature off, no cart, non-stockable items, no/inactive credit).
     */
    protected function companyCredit()
    {
        if (! $this->cart) {
            $this->setCart();
        }

        if (
            ! $this->getConfigData('active')
            || ! $this->cart
            || ! $this->cart->hasOnlyStockableItems()
        ) {
            return null;
        }

        $creditManager = app(CreditManager::class);

        if (! $creditManager->isActive()) {
            return null;
        }

        $credit = $creditManager->companyCreditFor($this->cart->customer);

        return ($credit && $credit->status) ? $credit : null;
    }

    /**
     * Get payment method image.
     *
     * @return string
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/cash-on-delivery.png', 'shop');
    }
}
