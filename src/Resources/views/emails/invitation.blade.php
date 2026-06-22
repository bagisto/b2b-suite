@php
    /**
     * Company invitation email. $model is the CompanyInvitation; $company, $role and
     * $accept_url are passed by the Notifier.
     */
@endphp

@component('shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.invitation.title', ['company' => $company])
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            {!! trans('b2b::app.emails.invitation.greeting', [
                'company' => '<strong>'.e($company).'</strong>',
                'role'    => '<strong>'.e($role ?: trans('b2b::app.emails.invitation.member')).'</strong>',
            ]) !!}
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.invitation.instruction')
        </p>
    </div>

    <a
        href="{{ $accept_url }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.invitation.cta')
    </a>

    @if ($model->expires_at)
        <p style="margin-top: 24px; font-size: 13px; color: #9CA3AF;">
            @lang('b2b::app.emails.invitation.expires', ['date' => core()->formatDate($model->expires_at, 'd M Y')])
        </p>
    @endif
@endcomponent
