<?php

use Aheadworks\Sarp2\Test\Integration\PaymentData\Braintree\Adapter\ClientStub;

return [
    'id' => 1,
    'merchantId' => 'merchant_id',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john_doe@example.com',
    'createdAt' => new \DateTime(),
    'updatedAt' => new \DateTime(),
    'creditCards' => [
        [
            'bin' => '100000',
            'cardType' => 'Visa',
            'cardholderName' => '',
            'commercial' => 'Unknown',
            'createdAt' => new \DateTime(),
            'customerId' => 1,
            'customerLocation' => 'US',
            'debit' => 'Yes',
            'default' => 1,
            'durbinRegulated' => 'Yes',
            'expirationMonth' => '05',
            'expirationYear' => '2025',
            'expired' => '',
            'healthcare' => 'Unknown',
            'imageUrl' => 'https://assets.braintreegateway.com/payment_method_logo/visa.png?environment=sandbox',
            'issuingBank' => 'Unknown',
            'last4' => '0004',
            'payroll' => 'Unknown',
            'prepaid' => 'No',
            'productId' => 'Unknown',
            'subscriptions' => [],
            'token' => ClientStub::TOKEN,
            'uniqueNumberIdentifier' => '123abc',
            'updatedAt' => new \DateTime()
        ]
    ],
    'addresses' => []
];
