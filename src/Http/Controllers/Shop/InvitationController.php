<?php

namespace Webkul\B2BSuite\Http\Controllers\Shop;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\View\View;
use Webkul\B2BSuite\Models\CompanyInvitation;
use Webkul\B2BSuite\Repositories\CompanyInvitationRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Shop\Http\Controllers\Controller;

class InvitationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected CompanyInvitationRepository $companyInvitationRepository,
        protected CustomerRepository $customerRepository,
    ) {}

    /**
     * Show the invitation so the logged-in invitee can accept or decline it.
     *
     * @return View|RedirectResponse
     */
    public function show(string $token)
    {
        $invitation = $this->resolve($token);

        if (! $invitation) {
            session()->flash('error', trans('b2b::app.shop.customers.account.invitations.invalid'));

            return redirect()->route('shop.customers.account.profile.index');
        }

        $company = $invitation->company;
        $role = $invitation->role;

        return view('b2b::shop.customers.account.invitations.show', compact('invitation', 'company', 'role'));
    }

    /**
     * Accept the invitation: the logged-in customer becomes a company user with the invited role.
     *
     * @return RedirectResponse
     */
    public function accept(string $token)
    {
        $invitation = $this->resolve($token);

        if (! $invitation) {
            session()->flash('error', trans('b2b::app.shop.customers.account.invitations.invalid'));

            return redirect()->route('shop.customers.account.profile.index');
        }

        $customer = $this->customerRepository->find(auth()->guard('customer')->user()->id);

        if ($customer->company_role_id || $customer->companies()->exists()) {
            session()->flash('error', trans('b2b::app.shop.customers.account.invitations.already-member'));

            return redirect()->route('shop.customers.account.profile.index');
        }

        Event::dispatch('customer.update.before', $customer->id);

        $customer = $this->customerRepository->update([
            'type' => 'user',
            'company_role_id' => $invitation->company_role_id,
        ], $customer->id);

        $customer->companies()->sync([$invitation->company_id]);

        Event::dispatch('customer.update.after', $customer);

        $this->companyInvitationRepository->update(['status' => CompanyInvitation::STATUS_ACCEPTED], $invitation->id);

        session()->flash('success', trans('b2b::app.shop.customers.account.invitations.accept-success'));

        return redirect()->route('shop.customers.account.profile.index');
    }

    /**
     * Decline the invitation.
     *
     * @return RedirectResponse
     */
    public function decline(string $token)
    {
        $invitation = $this->resolve($token);

        if ($invitation) {
            $this->companyInvitationRepository->update(['status' => CompanyInvitation::STATUS_DECLINED], $invitation->id);
        }

        session()->flash('success', trans('b2b::app.shop.customers.account.invitations.decline-success'));

        return redirect()->route('shop.customers.account.profile.index');
    }

    /**
     * Find a pending, unexpired invitation whose email matches the logged-in customer.
     */
    protected function resolve(string $token): ?CompanyInvitation
    {
        $invitation = $this->companyInvitationRepository->findOneWhere(['token' => $token]);

        if (
            ! $invitation
            || ! $invitation->isPending()
            || strcasecmp($invitation->email, (string) auth()->guard('customer')->user()->email) !== 0
        ) {
            return null;
        }

        return $invitation;
    }
}
