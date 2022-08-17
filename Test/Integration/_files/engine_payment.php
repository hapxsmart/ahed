<?php

require 'profile.php';

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var Profile $profile */
$profile = $objectManager->create(Profile::class);
/** @var ProfileResource $profileResource */
$profileResource = $objectManager->create(ProfileResource::class);
$profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

$profileId = $profile->getProfileId();

/** @var Schedule $schedule */
$schedule = $objectManager->create(Schedule::class);
$schedule->setTrialTotalCount(3)
    ->setRegularTotalCount(10);

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setProfileId($profileId)
    ->setProfile($profile)
    ->setType(PaymentInterface::TYPE_PLANNED)
    ->setPaymentPeriod(PaymentInterface::PERIOD_REGULAR)
    ->setPaymentStatus(PaymentInterface::STATUS_PLANNED)
    ->setScheduledAt('2018-05-18')
    ->setTotalScheduled(5.00)
    ->setBaseTotalScheduled(10.00)
    ->setPaymentData(['token_id' => 2])
    ->setSchedule($schedule);

/** @var Persistence $persistence */
$persistence = $objectManager->create(Persistence::class);
$payment = $persistence->save($payment);
