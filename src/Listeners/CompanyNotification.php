<?php

namespace Webkul\B2BSuite\Listeners;

use Webkul\B2BSuite\Notifications\Notifier;

class CompanyNotification
{
    /**
     * A new company has registered — alert the team that it awaits approval.
     */
    public function registered($company): void
    {
        Notifier::company($company, 'registered');
    }

    /**
     * A company has been approved — let the buyer know they can start ordering.
     */
    public function approved($company): void
    {
        Notifier::company($company, 'approved');
    }

    /**
     * A company has been disabled — inform the buyer.
     */
    public function disabled($company): void
    {
        Notifier::company($company, 'disabled');
    }
}
