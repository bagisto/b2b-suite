<?php

namespace Webkul\B2BSuite\Notifications;

use Illuminate\Support\Facades\Mail;
use Webkul\B2BSuite\Mail\Notification;
use Webkul\B2BSuite\Models\CustomerQuote;

/**
 * Central dispatcher for every B2B email notification. Each public method gates on the
 * master B2B switch and the per-notification admin toggle, resolves the recipient
 * (buyer = company, admin = assigned sales rep with a store-email fallback), then queues a
 * single generic {@see Notification} mailable. Keeping this in one place means the call sites
 * in controllers/listeners stay one-liners and the recipient/toggle rules live together.
 */
class Notifier
{
    /**
     * Quote type => admin-config toggle key.
     */
    public const QUOTE_TOGGLES = [
        'requested' => 'quote_requested',
        'sent'      => 'quotation_sent',
        'countered' => 'quote_countered',
        'accepted'  => 'quote_accepted',
        'rejected'  => 'quote_rejected',
        'message'   => 'quote_message',
        'ordered'   => 'quote_ordered',
        'po_placed' => 'purchase_order_placed',
    ];

    /**
     * Company type => toggle key + audience.
     */
    public const COMPANY_TOGGLES = [
        'registered' => ['company_registered', 'admin'],
        'approved'   => ['company_approved', 'buyer'],
        'disabled'   => ['company_disabled', 'buyer'],
    ];

    /**
     * Credit type => toggle key.
     */
    public const CREDIT_TOGGLES = [
        'updated'    => 'credit_updated',
        'reimbursed' => 'credit_reimbursed',
    ];

    /**
     * Notify about a quote/purchase-order lifecycle event.
     *
     * @param  string  $type      One of self::QUOTE_TOGGLES keys.
     * @param  string  $audience  "buyer" (company) or "admin" (sales rep).
     */
    public static function quote(CustomerQuote $quote, string $type, string $audience): void
    {
        $toggle = self::QUOTE_TOGGLES[$type] ?? null;

        if (! $toggle || ! self::enabled($toggle)) {
            return;
        }

        [$email, $name] = $audience === 'admin'
            ? self::salesRep($quote->company)
            : (self::buyer($quote->company) ?? [null, null]);

        if (! $email) {
            return;
        }

        $label = $quote->state === CustomerQuote::STATE_PURCHASE_ORDER ? $quote->po_number : $quote->quotation_number;

        self::send($email, $name, trans("b2b::app.emails.quote.$type.subject", ['id' => $label]), 'b2b::emails.quote', [
            'type'     => $type,
            'audience' => $audience,
        ], $quote);
    }

    /**
     * Notify about a company registration / approval / status change.
     */
    public static function company($company, string $type): void
    {
        $config = self::COMPANY_TOGGLES[$type] ?? null;

        if (! $config || ! self::enabled($config[0])) {
            return;
        }

        [$email, $name] = $config[1] === 'admin'
            ? self::salesRep($company)
            : (self::buyer($company) ?? [null, null]);

        if (! $email) {
            return;
        }

        self::send($email, $name, trans("b2b::app.emails.company.$type.subject", [
            'company' => $company->businessName() ?: $company->name,
        ]), 'b2b::emails.company', [
            'type'     => $type,
            'audience' => $config[1],
        ], $company);
    }

    /**
     * Notify a company about a credit-limit change or a reimbursement.
     *
     * @param  array  $meta  Extra scalars for the template (e.g. ['amount' => 100]).
     */
    public static function credit($company, string $type, array $meta = []): void
    {
        $toggle = self::CREDIT_TOGGLES[$type] ?? null;

        if (! $toggle || ! self::enabled($toggle)) {
            return;
        }

        [$email, $name] = self::buyer($company) ?? [null, null];

        if (! $email) {
            return;
        }

        self::send($email, $name, trans("b2b::app.emails.credit.$type.subject", [
            'company' => $company->businessName() ?: $company->name,
        ]), 'b2b::emails.credit', array_merge([
            'type'     => $type,
            'audience' => 'buyer',
        ], $meta), $company);
    }

    /**
     * Notify a newly created company sub-user.
     */
    public static function user($user): void
    {
        if (! self::enabled('user_created') || ! $user?->email) {
            return;
        }

        self::send($user->email, $user->name, trans('b2b::app.emails.user.created.subject'), 'b2b::emails.user', [
            'type'     => 'created',
            'audience' => 'buyer',
        ], $user);
    }

    /**
     * Notify a sub-user that they have been removed from their company and are now a standard
     * customer.
     */
    public static function userRemoved($user): void
    {
        if (! self::enabled('user_removed') || ! $user?->email) {
            return;
        }

        self::send($user->email, $user->name, trans('b2b::app.emails.user.removed.subject'), 'b2b::emails.user', [
            'type'     => 'removed',
            'audience' => 'buyer',
        ], $user);
    }

    /**
     * Email an invitee the link to join a company. Unlike the toggle-gated notifications this
     * is transactional (it drives the invite flow), so it is gated only by the master switch.
     */
    public static function invitation($invitation, string $companyName, ?string $roleName, string $acceptUrl): void
    {
        if (! core()->getConfigData('b2b.general.settings.active')) {
            return;
        }

        self::send($invitation->email, $invitation->email, trans('b2b::app.emails.invitation.subject', [
            'company' => $companyName,
        ]), 'b2b::emails.invitation', [
            'company'    => $companyName,
            'role'       => $roleName,
            'accept_url' => $acceptUrl,
        ], $invitation);
    }

    /**
     * Whether the master B2B switch and the given per-notification toggle are both on.
     */
    protected static function enabled(string $key): bool
    {
        if (! core()->getConfigData('b2b.general.settings.active')) {
            return false;
        }

        $value = core()->getConfigData('b2b.email_notifications.settings.'.$key);

        // Default to enabled when the toggle has never been saved.
        return $value === null ? true : (bool) $value;
    }

    /**
     * Resolve the buyer (company) recipient, or null when unavailable.
     */
    protected static function buyer($company): ?array
    {
        if (! $company || ! $company->email) {
            return null;
        }

        return [$company->email, $company->businessName() ?: $company->name];
    }

    /**
     * Resolve the admin recipient — the company's sales rep, else the store sender.
     */
    protected static function salesRep($company): array
    {
        $rep = $company?->salesRep;

        if ($rep && $rep->email) {
            return [$rep->email, $rep->name];
        }

        $sender = core()->getSenderEmailDetails();

        return [$sender['email'], $sender['name']];
    }

    /**
     * Queue the generic notification mailable, swallowing transport errors so a failed mail
     * never breaks the originating request.
     */
    protected static function send(string $email, ?string $name, string $subject, string $template, array $payload, $model): void
    {
        try {
            Mail::queue(new Notification($email, (string) $name, $subject, $template, $payload, $model));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
