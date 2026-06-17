<?php

use Webkul\B2BSuite\Payment\PayByCredit;

return [
    'paybycredit' => [
        'class' => PayByCredit::class,
        'code' => 'paybycredit',
        'title' => 'Pay By Credit',
        'description' => 'Pay later using your company credit.',
        'active' => true,
        'generate_invoice' => false,
        'sort' => 9,
    ],
];
