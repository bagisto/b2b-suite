@php
    /**
     * Company sub-user notification. $model is the customer; $type ∈ {created, removed}:
     *   created — welcome a newly added sub-user;
     *   removed — tell them they were detached and are now a standard customer.
     */
    $type ??= 'created';
@endphp

@component('shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.user.'.$type.'.title')
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.dear', ['name' => $model->name]) 👋
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            {!! trans('b2b::app.emails.user.'.$type.'.greeting', ['email' => '<strong>'.e($model->email).'</strong>']) !!}
        </p>
    </div>

    <a
        href="{{ route('shop.home.index') }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.user.'.$type.'.cta')
    </a>
@endcomponent
