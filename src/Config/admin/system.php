<?php

return [
    /**
     * B2B Suite.
     */
    [
        'key'  => 'b2b_suite',
        'name' => 'b2b_suite::app.admin.configuration.index.b2b-suite.title',
        'info' => 'b2b_suite::app.admin.configuration.index.b2b-suite.info',
        'sort' => 10,
    ], [
        'key'  => 'b2b_suite.general',
        'name' => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.title',
        'info' => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.info',
        'icon' => 'settings/store.svg',
        'sort' => 1,
    ], [
        'key'    => 'b2b_suite.general.settings',
        'name'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.settings.title',
        'info'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'active',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.settings.active',
                'type'          => 'boolean',
                'default'       => false,
                'channel_based' => true,
                'locale_based'  => false,
            ], [
                'name'          => 'no_requisition_list',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.general.settings.no-requisition-list',
                'depends'       => 'active:1',
                'validation'    => 'required_if:active,1|numeric|min:1',
                'type'          => 'number',
                'default'       => 1,
                'channel_based' => true,
            ],
        ],
    ], [
        'key'  => 'b2b_suite.quotes',
        'name' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.title',
        'info' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.info',
        'icon' => 'settings/store.svg',
        'sort' => 2,
    ], [
        'key'    => 'b2b_suite.quotes.settings',
        'name'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.title',
        'info'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'    => 'procurement_method',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.procurement-method.title',
                'type'    => 'select',
                'info'    => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.procurement-method.info',
                'options' => [
                    [
                        'name'  => 'manual ',
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.procurement-method.manual',
                        'value' => 'manual',
                    ], [
                        'name'  => 'automatic',
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.procurement-method.automatic',
                        'value' => 'automatic',
                    ],
                ],
            ], [
                'name'          => 'min_quantity',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.min-quantity',
                'depends'       => 'procurement_method:automatic',
                'validation'    => 'required_if:procurement_method,automatic|numeric|min:1',
                'type'          => 'number',
            ], [
                'name'          => 'quote_prefix',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.quote-prefix',
                'type'          => 'text',
                'info'          => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.quote-prefix-info',
                'validation'    => 'required|max:6',
                'default_value' => 'QO',
            ], [
                'name'          => 'po_prefix',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.po-prefix',
                'type'          => 'text',
                'info'          => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.po-prefix-info',
                'validation'    => 'required|max:6',
                'default_value' => 'PO',
            ], [
                'name'          => 'default_padding',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.default-padding',
                'type'          => 'number',
                'info'          => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.default-padding-info',
                'validation'    => 'required',
                'default_value' => 9,
            ], [
                'name'          => 'minimum_amount',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.minimum-amount',
                'type'          => 'text',
                'validation'    => 'numeric|min:0',
                'default'       => '0',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'minimum_amount_message',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.minimum-amount-message',
                'type'          => 'textarea',
                'default'       => 'The cart total must meet the minimum amount before a quote can be requested.',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'default_expiration_period',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.default-expiration-period',
                'type'          => 'text',
                'validation'    => 'numeric|min:1',
                'default'       => '30',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'expiration_period_unit',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.expiration-period-unit',
                'type'          => 'select',
                'options'       => [
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.days',
                        'value' => 'days',
                    ],
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.weeks',
                        'value' => 'weeks',
                    ],
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.months',
                        'value' => 'months',
                    ],
                ],
                'default'       => 'days',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'supported_file_formats',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.supported-file-formats',
                'type'          => 'text',
                'default'       => 'doc,docx,xls,xlsx,pdf,txt,jpg,png,jpeg',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'          => 'maximum_file_size',
                'title'         => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.maximum-file-size',
                'type'          => 'text',
                'validation'    => 'numeric|min:1',
                'default'       => '10',
                'channel_based' => true,
                'locale_based'  => false,
            ],
            [
                'name'    => 'can_customer_approve_quote',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.approve-quote-title',
                'info'    => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.settings.approve-quote-info',
                'type'    => 'boolean',
                'default' => false,
            ],
        ],
    ], [
        'key'    => 'b2b_suite.quotes.email_option',
        'name'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.title',
        'info'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.info',
        'sort'   => 3,
        'fields' => [
            [
                'name'    => 'quotation_template',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.quotation-template.title',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.quotation-template.default',
                        'value' => 1,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.quotation-template.pickup-order',
                        'value' => 2,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.quotation-template.pickup-order-guest',
                        'value' => 3,
                    ],
                ],
            ], [
                'name'    => 'purchase_order_template',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.purchase-order-template.title',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.purchase-order-template.default',
                        'value' => 1,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.purchase-order-template.pickup-order',
                        'value' => 2,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.purchase-order-template.pickup-order-guest',
                        'value' => 3,
                    ],
                ],
            ], [
                'name'    => 'cancel_order_template',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.cancel-order-template.title',
                'type'    => 'select',
                'options' => [
                    [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.cancel-order-template.default',
                        'value' => 1,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.cancel-order-template.pickup-order',
                        'value' => 2,
                    ], [
                        'title' => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.cancel-order-template.pickup-order-guest',
                        'value' => 3,
                    ],
                ],
            ], [
                'name'    => 'mail_from_address',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.from-address',
                'type'    => 'text',
            ], [
                'name'    => 'mail_from_title',
                'title'   => 'b2b_suite::app.admin.configuration.index.b2b-suite.quotes.email-options.from-name',
                'type'    => 'text',
            ],
        ],
    ],
];
