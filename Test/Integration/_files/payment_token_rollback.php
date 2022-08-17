<?php

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var Token $token */
$token = $objectManager->create(Token::class);
/** @var TokenResource $tokenResource */
$tokenResource = $objectManager->create(TokenResource::class);
$tokenResource->load($token, 'braintree', PaymentTokenInterface::PAYMENT_METHOD);
if ($token->getId()) {
    $tokenResource->delete($token);
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
