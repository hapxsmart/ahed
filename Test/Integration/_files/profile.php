<?php

require 'payment_token.php';
require 'plan.php';

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Plan;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\Profile\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Test\Integration\Model\Profile\ValidatorStub;
use Aheadworks\Sarp2\Test\Integration\Model\ResourceModel\ProfileStub as ProfileResourceStub;
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

/** @var Plan $plan */
$plan = $objectManager->create(Plan::class);
/** @var PlanResource $planResource */
$planResource = $objectManager->create(PlanResource::class);
$planResource->load($plan, 'Subscription Plan', PlanInterface::NAME);

/** @var Token $token */
$token = $objectManager->create(Token::class);
/** @var TokenResource $tokenResource */
$tokenResource = $objectManager->create(TokenResource::class);
$tokenResource->load($token, 'braintree', PaymentTokenInterface::PAYMENT_METHOD);

/** @var ProfileInterface $profile */
$profile = $objectManager->create(ProfileInterface::class);
$profile->setIncrementId('000000001')
    ->setStoreId(1)
    ->setStatus(Status::PENDING)
    ->setPlanId($plan->getPlanId())
    ->setPlanName($plan->getName())
    ->setPlanDefinitionId($plan->getDefinitionId())
    ->setPaymentTokenId($token->getTokenId())
    ->setInitialGrandTotal(5.00)
    ->setBaseInitialGrandTotal(7.25)
    ->setTrialGrandTotal(10.00)
    ->setBaseTrialGrandTotal(15.00)
    ->setRegularGrandTotal(20.00)
    ->setBaseRegularGrandTotal(30.00);

/** @var ProfileRepositoryInterface $profileRepository */
$profileRepository = $objectManager->create(ProfileRepositoryInterface::class);
$profileRepository->save($profile, false);
