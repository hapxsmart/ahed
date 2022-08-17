<?php
namespace Aheadworks\Sarp2\Test\Integration\Engine\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status as ProfileStatus;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment as PaymentResource;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Schedule as ScheduleResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class PersistenceTest
 * @package Aheadworks\Sarp2\Test\Integration\Engine\Payment
 */
class PersistenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    /**
     * @var PaymentResource
     */
    private $paymentResource;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->persistence = $this->objectManager->create(Persistence::class);
        $this->profileResource = $this->objectManager->create(ProfileResource::class);
        $this->scheduleResource = $this->objectManager->create(ScheduleResource::class);
        $this->paymentResource = $this->objectManager->create(PaymentResource::class);
    }


    public function testCreate()
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        $profileId = $profile->getProfileId();

        /** @var Schedule $schedule */
        $schedule = $this->objectManager->create(Schedule::class);
        $schedule->setIsInitialPaid(true)
            ->setTrialCount(1)
            ->setTrialTotalCount(3)
            ->setRegularTotalCount(10);

        /** @var Payment $payment */
        $payment = $this->objectManager->create(Payment::class);
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

        $payment = $this->persistence->save($payment);
        $paymentId = $payment->getId();
        $this->assertNotNull($paymentId);

        $loadedPayment = $this->persistence->get($paymentId);
        $this->assertEquals($paymentId, $loadedPayment->getId());
        $this->assertEquals($profileId, $loadedPayment->getProfileId());
        $this->assertEquals($payment->getType(), $loadedPayment->getType());
        $this->assertEquals($payment->getPaymentPeriod(), $loadedPayment->getPaymentPeriod());
        $this->assertEquals($payment->getPaymentStatus(), $loadedPayment->getPaymentStatus());
        $this->assertEquals($payment->getScheduledAt(), $loadedPayment->getScheduledAt());
        $this->assertEquals($payment->getTotalScheduled(), $loadedPayment->getTotalScheduled());
        $this->assertEquals($payment->getPaymentData(), $loadedPayment->getPaymentData());

        $loadedSchedule = $loadedPayment->getSchedule();
        $scheduleId = $loadedSchedule->getScheduleId();
        $this->assertNotNull($scheduleId);
        $this->assertEquals($scheduleId, $loadedPayment->getScheduleId());
        $this->assertEquals($schedule->isInitialPaid(), $loadedSchedule->isInitialPaid());
        $this->assertEquals($schedule->getTrialCount(), $loadedSchedule->getTrialCount());
        $this->assertEquals($schedule->getTrialTotalCount(), $loadedSchedule->getTrialTotalCount());
        $this->assertEquals(0, $loadedSchedule->getRegularCount());
        $this->assertEquals($schedule->getRegularTotalCount(), $loadedSchedule->getRegularTotalCount());
        $this->assertEquals(false, $loadedSchedule->isReactivated());

        $loadedProfile = $loadedPayment->getProfile();
        $this->assertEquals($profileId, $loadedProfile->getProfileId());
    }

    /**
     * @param string $setter
     * @param string $getter
     * @param mixed $value
     * @dataProvider updatePaymentDataProvider
     */
    public function testUpdatePaymentData($setter, $getter, $value)
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        /** @var Schedule $schedule */
        $schedule = $this->objectManager->create(Schedule::class);
        $this->scheduleResource->load($schedule, $profile->getProfileId(), 'profile_id');

        /** @var Payment $payment */
        $payment = $this->objectManager->create(Payment::class);
        $this->paymentResource->load($payment, $schedule->getScheduleId(), 'schedule_id');
        $paymentId = $payment->getId();

        $payment->$setter($value);
        $this->persistence->save($payment);
        $loadedPayment = $this->persistence->get($paymentId);

        $this->assertEquals($value, $loadedPayment->$getter());
    }

    /**
     * @param string $setter
     * @param string $getter
     * @param mixed $value
     * @dataProvider updateScheduleDataDataProvider
     */
    public function testUpdateScheduleData($setter, $getter, $value)
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);

        /** @var Schedule $schedule */
        $schedule = $this->objectManager->create(Schedule::class);
        $this->scheduleResource->load($schedule, $profile->getProfileId(), 'profile_id');
        $scheduleId = $schedule->getScheduleId();

        /** @var Payment $payment */
        $payment = $this->objectManager->create(Payment::class);
        $this->paymentResource->load($payment, $scheduleId, 'schedule_id');

        $paymentSchedule = $payment->getSchedule();
        $paymentSchedule->$setter($value);
        $this->persistence->save($payment);

        /** @var Schedule $loadedSchedule */
        $loadedSchedule = $this->objectManager->create(Schedule::class);
        $this->scheduleResource->load($loadedSchedule, $scheduleId);

        $this->assertEquals($value, $loadedSchedule->$getter());
    }

    public function testUpdateProfileData()
    {
        /** @var Profile $profile */
        $profile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($profile, '000000001', ProfileInterface::INCREMENT_ID);
        $profileId = $profile->getProfileId();

        /** @var Schedule $schedule */
        $schedule = $this->objectManager->create(Schedule::class);
        $this->scheduleResource->load($schedule, $profileId, 'profile_id');

        /** @var Payment $payment */
        $payment = $this->objectManager->create(Payment::class);
        $this->paymentResource->load($payment, $schedule->getScheduleId(), 'schedule_id');

        $paymentProfile = $payment->getProfile();
        $paymentProfile->setStatus(ProfileStatus::ACTIVE);

        $this->persistence->save($payment);
        /** @var Profile $loadedProfile */
        $loadedProfile = $this->objectManager->create(Profile::class);
        $this->profileResource->load($loadedProfile, $profileId);

        $this->assertEquals(ProfileStatus::ACTIVE, $loadedProfile->getStatus());
    }

    /**
     * @return array
     */
    public function updatePaymentDataProvider()
    {
        return [
            ['setPaymentStatus', 'getPaymentStatus', PaymentInterface::STATUS_CANCELLED],
            ['setRetryAt', 'getRetryAt', '2018-05-22'],
            ['setRetriesCount', 'getRetriesCount', 1],
            ['setPaymentData', 'getPaymentData', ['token_id' => 6]]
        ];
    }

    /**
     * @return array
     */
    public function updateScheduleDataDataProvider()
    {
        return [
            ['setIsInitialPaid', 'isInitialPaid', true],
            ['setTrialCount', 'getTrialCount', 1],
            ['setRegularCount', 'getRegularCount', 1],
            ['setIsReactivated', 'isReactivated', true]
        ];
    }
}
