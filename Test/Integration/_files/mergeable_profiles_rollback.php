<?php

require 'plan_rollback.php';

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProfileResource $profileResource */
$profileResource = $objectManager->create(ProfileResource::class);
/** @var TokenResource $tokenResource */
$tokenResource = $objectManager->create(TokenResource::class);

$incrementIds = ['000000003', '000000004'];
foreach ($incrementIds as $incrementId) {
    /** @var Profile $profile */
    $profile = $objectManager->create(Profile::class);

    /** @var Token $token */
    $token = $objectManager->create(Token::class);
    $tokenResource->load($token, $profile->getPaymentTokenId());
    if ($token->getId()) {
        $tokenResource->delete($token);
    }

    $profileResource->load($profile, $incrementId, ProfileInterface::INCREMENT_ID);
    if ($profile->getProfileId()) {
        $profileResource->delete($profile);
    }
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
