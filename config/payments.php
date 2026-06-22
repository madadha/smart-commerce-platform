<?php

use App\Payments\Gateways\ManualPaymentGateway;
use App\Payments\Gateways\PayPlusPaymentGateway;

return [
    'gateways' => [
        'manual' => ManualPaymentGateway::class,
        'payplus' => PayPlusPaymentGateway::class,
    ],

    'methods' => [
        'cash' => [
            'enabled' => true,
            'provider' => 'manual',
            'translation_key' => 'storefront.checkout.cash',
        ],
        'bank_transfer' => [
            'enabled' => true,
            'provider' => 'manual',
            'translation_key' => 'storefront.checkout.bank_transfer',
        ],
        'credit_card' => [
            'enabled' => false,
            'provider' => null,
            'translation_key' => 'storefront.checkout.credit_card',
        ],
        'paypal' => [
            'enabled' => false,
            'provider' => null,
            'translation_key' => 'storefront.checkout.paypal',
        ],
        'payplus' => [
            'enabled' => false,
            'provider' => null,
            'translation_key' => 'storefront.checkout.payplus',
        ],
        'stripe' => [
            'enabled' => false,
            'provider' => null,
            'translation_key' => 'storefront.checkout.stripe',
        ],
    ],
];
