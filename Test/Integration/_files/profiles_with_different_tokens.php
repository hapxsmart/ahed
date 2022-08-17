<?php

require 'plan.php';

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\Profile\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Test\Integration\Model\Profile\ValidatorStub;
use Aheadworks\Sarp2\Test\Integration\Model\ResourceModel\ProfileStub as ProfileResourceStub;
use Aheadworks\Sarp2\Test\Integration\PaymentData\Braintree\Adapter\ClientStub;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$objectManager->configure(
    [
        'preferences' => [
            ProfileResource::class => ProfileResourceStub::class,
            Validator::class => ValidatorStub::class
        ]
    ]
);
$objectManager->removeSharedInstance(ProfileResourceStub::class);
$objectManager->removeSharedInstance(Validator::class);

/** @var PaymentTokenRepositoryInterface $tokenRepository */
$tokenRepository = $objectManager->create(PaymentTokenRepositoryInterface::class);
/** @var PlanResource $planResource */
$planResource = $objectManager->create(PlanResource::class);
/** @var ProfileRepositoryInterface $profileRepository */
$profileRepository = $objectManager->create(ProfileRepositoryInterface::class);

$paymentTokensCount = 2;
$profileCounter = 1;
while ($paymentTokensCount > 0) {
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
    $tokenRepository->save($paymentToken);

    /** @var Plan $plan */
    $plan = $objectManager->create(Plan::class);
    $planResource->load($plan, 'Subscription Plan', PlanInterface::NAME);

    /** @var ProfileInterface $profile */
    $profile = $objectManager->create(ProfileInterface::class);
    $profile->setIncrementId('00000000' . $profileCounter)
        ->setStoreId(1)
        ->setStatus(Status::ACTIVE)
        ->setPlanId($plan->getPlanId())
        ->setPlanName($plan->getName())
        ->setPlanDefinitionId($plan->getDefinitionId())
        ->setPaymentTokenId($paymentToken->getTokenId())
        ->setInitialGrandTotal(5.00)
        ->setBaseInitialGrandTotal(7.25)
        ->setTrialGrandTotal(10.00)
        ->setBaseTrialGrandTotal(15.00)
        ->setRegularGrandTotal(20.00)
        ->setBaseRegularGrandTotal(30.00)
        ->setPaymentMethod('braintree')
        ->setCheckoutShippingMethod('flatrate_flatrate')
        ->setCustomerId(null)
        ->setCustomerEmail('customer@example.com')
        ->setCustomerIsGuest(true)
        ->setIsVirtual(false);
    $profileCounter++;
    $paymentTokensCount--;

    $profileRepository->save($profile, false);
}
