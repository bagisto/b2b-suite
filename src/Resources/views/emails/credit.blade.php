@php
    /**
     * Company-credit notification (buyer audience). $model is the company customer, $type ∈
     * {updated, reimbursed}. Optional scalars: $amount, $credit_limit, $available.
     */
    $companyName = $model->businessName() ?: $model->name;
@endphp

@component('shop::emails.layout')
    <div style="margin-bottom: 28px;">
        <span style="font-size: 22px; font-weight: 600; color: #121A26;">
            @lang('b2b::app.emails.credit.'.$type.'.title')
        </span>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.dear', ['name' => $companyName]) 👋
        </p>

        <p style="font-size: 16px; color: #5E5E5E; line-height: 24px;">
            @lang('b2b::app.emails.credit.'.$type.'.greeting')
        </p>
    </div>

    <table style="border-collapse: collapse; width: 100%; margin-bottom: 28px;">
        <tbody style="font-size: 15px; color: #384860;">
            @if (isset($amount))
                <tr style="border-top: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 12px 4px; color: #6B7280;">
                        @lang('b2b::app.emails.credit.'.($type === 'reimbursed' ? 'amount-reimbursed' : 'new-limit'))
                    </td>
                    <td style="padding: 12px 4px; text-align: right; font-weight: 700; color: #121A26;">
                        {{ core()->formatBasePrice($amount) }}
                    </td>
                </tr>
            @endif

            @if (isset($available))
                <tr style="border-bottom: 1px solid #E5E7EB;">
                    <td style="padding: 12px 4px; color: #6B7280;">@lang('b2b::app.emails.credit.available')</td>
                    <td style="padding: 12px 4px; text-align: right; font-weight: 600; color: #121A26;">
                        {{ core()->formatBasePrice($available) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <a
        href="{{ route('shop.home.index') }}"
        style="display: inline-block; background-color: #0E5FD9; color: #FFFFFF; font-size: 15px; font-weight: 600; padding: 11px 22px; border-radius: 6px; text-decoration: none;"
    >
        @lang('b2b::app.emails.credit.cta')
    </a>
@endcomponent
