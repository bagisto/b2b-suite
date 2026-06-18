@php
    /**
     * New company sub-user welcome notification. $model is the newly created sub-user customer.
     */
@endphp

@component('shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.user.created.title')
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.dear', ['name' => $model->name]) 👋
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            {!! trans('b2b::app.emails.user.created.greeting', ['email' => '<strong>'.e($model->email).'</strong>']) !!}
        </p>
    </div>

    <a
        href="{{ route('shop.home.index') }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.user.created.cta')
    </a>
@endcomponent
