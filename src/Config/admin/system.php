<?php

return [
    /**
     * B2B Suite (root section).
     */
    [
        'key' => 'b2b',
        'name' => 'b2b::app.admin.configuration.index.b2b.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.info',
        'sort' => 2,
    ],

    /**
     * General.
     */
    [
        'key' => 'b2b.general',
        'name' => 'b2b::app.admin.configuration.index.b2b.general.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.general.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key' => 'b2b.general.settings',
        'name' => 'b2b::app.admin.configuration.index.b2b.general.settings.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.general.settings.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'active',
                'title' => 'b2b::app.admin.configuration.index.b2b.general.settings.active',
                'type' => 'boolean',
                'default' => false,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'require_company_approval',
                'title' => 'b2b::app.admin.configuration.index.b2b.general.settings.require-company-approval',
                'info' => 'b2b::app.admin.configuration.index.b2b.general.settings.require-company-approval-info',
                'depends' => 'active:1',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'no_requisition_list',
                'title' => 'b2b::app.admin.configuration.index.b2b.general.settings.no-requisition-list',
                'depends' => 'active:1',
                'validation' => 'required_if:active,1|numeric|min:1',
                'type' => 'number',
                'default' => 1,
                'channel_based' => true,
            ],
        ],
    ],

    /**
     * Quotations & Purchase Orders.
     */
    [
        'key' => 'b2b.quotes',
        'name' => 'b2b::app.admin.configuration.index.b2b.quotes.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.quotes.info',
        'icon' => 'settings/store.svg',
        'sort' => 2,
    ], [
        'key' => 'b2b.quotes.settings',
        'name' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.info',
        'sort' => 1,
        'fields' => [
            /**
             * Numbering.
             */
            [
                'name' => 'quote_prefix',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.quote-prefix',
                'info' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.quote-prefix-info',
                'type' => 'text',
                'validation' => 'required|max:6',
                'default' => 'QO',
            ], [
                'name' => 'po_prefix',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.po-prefix',
                'info' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.po-prefix-info',
                'type' => 'text',
                'validation' => 'required|max:6',
                'default' => 'PO',
            ], [
                'name' => 'default_padding',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.default-padding',
                'info' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.default-padding-info',
                'type' => 'number',
                'validation' => 'required',
                'default' => 9,
            ],

            /**
             * Expiration.
             */
            [
                'name' => 'default_expiration_period',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.default-expiration-period',
                'type' => 'text',
                'validation' => 'numeric|min:1',
                'default' => '30',
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'expiration_period_unit',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.expiration-period-unit',
                'type' => 'select',
                'options' => [
                    [
                        'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.days',
                        'value' => 'days',
                    ], [
                        'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.weeks',
                        'value' => 'weeks',
                    ], [
                        'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.months',
                        'value' => 'months',
                    ],
                ],
                'default' => 'days',
                'channel_based' => true,
                'locale_based' => false,
            ],

            /**
             * Minimum cart amount to request a quote.
             */
            [
                'name' => 'minimum_amount',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.minimum-amount',
                'type' => 'text',
                'validation' => 'numeric|min:0',
                'default' => '0',
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'minimum_amount_message',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.minimum-amount-message',
                'type' => 'textarea',
                'default' => 'The cart total must meet the minimum amount before a quote can be requested.',
                'channel_based' => true,
                'locale_based' => true,
            ],

            /**
             * Attachments.
             */
            [
                'name' => 'supported_file_formats',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.supported-file-formats',
                'type' => 'text',
                'default' => 'doc,docx,xls,xlsx,pdf,txt,jpg,png,jpeg',
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'maximum_file_size',
                'title' => 'b2b::app.admin.configuration.index.b2b.quotes.settings.maximum-file-size',
                'type' => 'text',
                'validation' => 'numeric|min:1',
                'default' => '10',
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],

    /**
     * Company Credit.
     */
    [
        'key' => 'b2b.credit',
        'name' => 'b2b::app.admin.configuration.index.b2b.credit.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.credit.info',
        'icon' => 'settings/store.svg',
        'sort' => 3,
    ], [
        'key' => 'b2b.credit.settings',
        'name' => 'b2b::app.admin.configuration.index.b2b.credit.settings.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.credit.settings.info',
        'sort' => 1,
        'fields' => [
            [
                'name' => 'active',
                'title' => 'b2b::app.admin.configuration.index.b2b.credit.settings.active',
                'info' => 'b2b::app.admin.configuration.index.b2b.credit.settings.active-info',
                'type' => 'boolean',
                'default' => false,
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],

    /**
     * B2B email notifications — one admin toggle per notification (all enabled by default).
     */
    [
        'key' => 'b2b.email_notifications',
        'name' => 'b2b::app.admin.configuration.index.b2b.email-notifications.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.info',
        'icon' => 'settings/store.svg',
        'sort' => 4,
    ], [
        'key' => 'b2b.email_notifications.settings',
        'name' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.title',
        'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.info',
        'sort' => 1,
        'fields' => [
            /**
             * Company onboarding.
             */
            [
                'name' => 'company_registered',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-registered',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-registered-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'company_approved',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-approved',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-approved-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'company_disabled',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-disabled',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.company-disabled-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'user_created',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.user-created',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.user-created-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ],

            /**
             * Company credit.
             */
            [
                'name' => 'credit_updated',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.credit-updated',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.credit-updated-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'credit_reimbursed',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.credit-reimbursed',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.credit-reimbursed-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ],

            /**
             * Quote negotiation (in flow order).
             */
            [
                'name' => 'quote_requested',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-requested',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-requested-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quotation_sent',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quotation-sent',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quotation-sent-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quote_countered',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-countered',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-countered-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quote_message',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-message',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-message-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quote_accepted',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-accepted',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-accepted-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quote_rejected',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-rejected',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-rejected-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'quote_ordered',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-ordered',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.quote-ordered-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'purchase_order_placed',
                'title' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.purchase-order-placed',
                'info' => 'b2b::app.admin.configuration.index.b2b.email-notifications.settings.purchase-order-placed-info',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],

    /**
     * Pay By Credit payment method (visible only to companies with credit).
     */
    [
        'key' => 'sales.payment_methods.paybycredit',
        'name' => 'b2b::app.admin.configuration.index.sales.payment-methods.pay-by-credit',
        'info' => 'b2b::app.admin.configuration.index.sales.payment-methods.pay-by-credit-info',
        'sort' => 9,
        'fields' => [
            [
                'name' => 'active',
                'title' => 'admin::app.configuration.index.sales.payment-methods.status',
                'type' => 'boolean',
                'default' => true,
                'channel_based' => true,
                'locale_based' => false,
            ], [
                'name' => 'title',
                'title' => 'admin::app.configuration.index.sales.payment-methods.title',
                'type' => 'text',
                'default' => 'Pay By Credit',
                'depends' => 'active:1',
                'channel_based' => true,
                'locale_based' => true,
            ], [
                'name' => 'description',
                'title' => 'admin::app.configuration.index.sales.payment-methods.description',
                'type' => 'textarea',
                'default' => 'Pay later using your company credit.',
                'depends' => 'active:1',
                'channel_based' => true,
                'locale_based' => true,
            ], [
                'name' => 'sort',
                'title' => 'admin::app.configuration.index.sales.payment-methods.sort-order',
                'type' => 'number',
                'default' => 9,
                'depends' => 'active:1',
                'channel_based' => true,
                'locale_based' => false,
            ],
        ],
    ],
];
