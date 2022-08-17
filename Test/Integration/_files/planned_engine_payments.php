<?php

require 'mergeable_profiles.php';
require 'profiles_with_different_tokens.php';

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/** @var ProfileResource $profileResource */
$profileResource = $objectManager->create(ProfileResource::class);
/** @var Persistence $persistence */
$persistence = $objectManager->create(Persistence::class);

$incrementIds = ['000000001', '000000002', '000000003', '000000004'];
$paymentData = [
    '000000001' => [
        'payment_period' => PaymentInterface::PERIOD_REGULAR,
        'total_scheduled' => 5.00,
        'base_total_scheduled' => 10.00
    ],
    '000000002' => [
        'payment_period' => PaymentInterface::PERIOD_TRIAL,
        'total_scheduled' => 3.00,
        'base_total_scheduled' => 6.00
    ],
    '000000003' => [
        'payment_period' => PaymentInterface::PERIOD_REGULAR,
        'total_scheduled' => 6.00,
        'base_total_scheduled' => 12.00
    ],
    '000000004' => [
        'payment_period' => PaymentInterface::PERIOD_TRIAL,
        'total_scheduled' => 2.00,
        'base_total_scheduled' => 4.00
    ]
];
foreach ($incrementIds as $incrementId) {
    /** @var Profile $profile */
    $profile = $objectManager->create(Profile::class);
    $profileResource->load($profile, $incrementId, ProfileInterface::INCREMENT_ID);
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
        ->setPaymentStatus(PaymentInterface::STATUS_PLANNED)
        ->setScheduledAt('2018-05-18')
        ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
        ->setSchedule($schedule);
    $payment->addData($paymentData[$incrementId]);
    $payment = $persistence->save($payment);
}
