<?php

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Test\Integration\PaymentData\Braintree\Adapter\ClientStub;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var PaymentTokenInterface $paymentToken */
$paymentToken = $objectManager->create(PaymentTokenInterface::class);
$paymentToken
    ->setType('card')
    ->setPaymentMethod('braintree')
    ->setTokenValue(ClientStub::TOKEN);

$expiresAt = (new \DateTime())
    ->add(new \DateInterval('P5Y'))
    ->format('Y-m-d 00:00:00');
$paymentToken->setExpiresAt($expiresAt);

/** @var PaymentTokenRepositoryInterface $tokenRepository */
$tokenRepository = $objectManager->create(PaymentTokenRepositoryInterface::class);
$tokenRepository->save($paymentToken);
