@php
    /**
     * Quote / purchase-order notification body. $model is the CustomerQuote, $type selects the
     * wording, $audience ("buyer"|"admin") selects the layout and the deep link.
     */
    $isPo = $model->state === \Webkul\B2BSuite\Models\CustomerQuote::STATE_PURCHASE_ORDER;
    $ref  = $isPo ? $model->po_number : $model->quotation_number;

    if ($audience === 'admin') {
        $ctaUrl = $isPo
            ? route('admin.b2b.purchase_orders.view', $model->id)
            : route('admin.b2b.quotes.view', $model->id);
    } else {
        $ctaUrl = $isPo
            ? route('shop.customers.account.purchase_orders.view', $model->id)
            : route('shop.customers.account.quotes.view', $model->id);
    }

    $recipientName = $audience === 'admin'
        ? ($model->company?->salesRep?->name ?: trans('b2b::app.emails.team'))
        : ($model->company?->businessName() ?: $model->company?->name);

    $linkedRef = '<a href="'.$ctaUrl.'" style="color: #2969FF; text-decoration: none;">#'.$ref.'</a>';
@endphp

@component($audience === 'admin' ? 'admin::emails.layout' : 'shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.quote.'.$type.'.title')
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.dear', ['name' => $recipientName]) 👋
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            {!! trans('b2b::app.emails.quote.'.$type.'.greeting', ['ref' => $linkedRef]) !!}
        </p>
    </div>

    <table style="border-collapse: collapse; width: 100%; margin-bottom: 28px;">
        <tbody style="font-size: 15px; color: #384860;">
            <tr style="border-top: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB;">
                <td style="padding: 12px 4px; color: #6B7280;">@lang('b2b::app.emails.quote.reference')</td>
                <td style="padding: 12px 4px; text-align: right; font-weight: 600; color: #121A26;">#{{ $ref }}</td>
            </tr>

            @if ($model->name)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 12px 4px; color: #6B7280;">@lang('b2b::app.emails.quote.name')</td>
                    <td style="padding: 12px 4px; text-align: right; color: #121A26;">{{ $model->name }}</td>
                </tr>
            @endif

            <tr style="border-bottom: 1px solid #E5E7EB;">
                <td style="padding: 12px 4px; color: #6B7280;">@lang('b2b::app.emails.quote.status')</td>
                <td style="padding: 12px 4px; text-align: right; color: #121A26;">@lang('b2b::app.admin.quotes.view.'.$model->status)</td>
            </tr>

            @if ($model->negotiated_total)
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 12px 4px; color: #6B7280;">@lang('b2b::app.emails.quote.total')</td>
                    <td style="padding: 12px 4px; text-align: right; font-weight: 700; color: #121A26;">
                        {{ core()->formatBasePrice($model->negotiated_total) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <a
        href="{{ $ctaUrl }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.quote.cta')
    </a>
@endcomponent
