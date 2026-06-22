@php
    /**
     * Company registration / approval / status notification. $model is the company customer,
     * $type ∈ {registered, approved, disabled}, $audience selects layout + deep link.
     */
    $companyName = $model->businessName() ?: $model->name;

    if ($audience === 'admin') {
        $ctaUrl       = route('admin.b2b.companies.edit', $model->id);
        $recipientName = $model->salesRep?->name ?: trans('b2b::app.emails.team');
    } else {
        $ctaUrl       = route('shop.home.index');
        $recipientName = $companyName;
    }
@endphp

@component($audience === 'admin' ? 'admin::emails.layout' : 'shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.company.'.$type.'.title')
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.dear', ['name' => $recipientName]) 👋
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            {!! trans('b2b::app.emails.company.'.$type.'.greeting', ['company' => '<strong>'.e($companyName).'</strong>']) !!}
        </p>
    </div>

    <a
        href="{{ $ctaUrl }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.company.'.$type.'.cta')
    </a>
@endcomponent
