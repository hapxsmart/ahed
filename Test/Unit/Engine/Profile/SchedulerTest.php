<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile;

use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\EngineInterface;
use Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Initial;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Profile\Scheduler;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Profile as ProfileResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Phrase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\Scheduler
 */
class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineMock;

    /**
     * @var PaymentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentListMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var Initial|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorMock;

    /**
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorSourceFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationManagerMock;

    /**
     * @var ProfileResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileResourceMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->engineMock = $this->createMock(EngineInterface::class);
        $this->paymentListMock = $this->createMock(PaymentsList::class);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->generatorMock = $this->createMock(Initial::class);
        $this->generatorSourceFactoryMock = $this->createMock(SourceFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->notificationManagerMock = $this->createMock(Manager::class);
        $this->profileResourceMock = $this->createMock(ProfileResource::class);
        $this->scheduler = $objectManager->getObject(
            Scheduler::class,
            [
                'engine' => $this->engineMock,
                'paymentList' => $this->paymentListMock,
                'persistence' => $this->persistenceMock,
                'generator' => $this->generatorMock,
                'generatorSourceFactory' => $this->generatorSourceFactoryMock,
                'logger' => $this->loggerMock,
                'notificationManager' => $this->notificationManagerMock,
                'profileResource' => $this->profileResourceMock
            ]
        );
    }

    public function testSchedule()
    {
        $profileId = 1;
        $paymentId = 2;
        $scheduleId = 3;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        $prePaymentInfoMock = $this->createMock(PrePaymentInfoInterface::class);
        $generatorSourceMock = $this->createMock(SourceInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $scheduleMock = $this->createMock(Schedule::class);

        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentListMock->expects($this->once())
            ->method('hasForProfile')
            ->with($profileId)
            ->willReturn(false);
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['profile' => $profileMock])
            ->willReturn($generatorSourceMock);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with($generatorSourceMock)
            ->willReturn([$paymentMock]);
        $profileMock->expects($this->once())
            ->method('getPrePaymentInfo')
            ->willReturn($prePaymentInfoMock);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsInitialFeePaid')
            ->willReturn(true);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::ACTIVE);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::ACTIVE);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $paymentMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $this->loggerMock->expects($this->exactly(2))
            ->method('traceSchedule')
            ->withConsecutive(
                [
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['profileId' => $profileId],
                    ['profileStatus' => Status::ACTIVE]
                ],
                [
                    LoggerInterface::ENTRY_PAYMENTS_SCHEDULED,
                    ['profileId' => $profileId],
                    ['payments' => [$paymentMock]]
                ]
            );
        $this->notificationManagerMock->expects($this->exactly(2))
            ->method('schedule')
            ->withConsecutive(
                [
                    NotificationInterface::TYPE_SUBSCRIPTION_CREATED,
                    $paymentMock
                ],
                [
                    NotificationInterface::TYPE_UPCOMING_BILLING,
                    $paymentMock
                ]
            );
        $this->engineMock->expects($this->once())
            ->method('processPayments')
            ->with([$paymentId]);

        $this->assertEquals([$profileMock], $this->scheduler->schedule([$profileMock]));
    }

    public function testScheduleNotSaved()
    {
        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn(null);
        $this->generatorMock->expects($this->never())
            ->method('generate');
        $this->persistenceMock->expects($this->never())
            ->method('save');
        $this->engineMock->expects($this->once())
            ->method('processPayments')
            ->with([]);
        $this->assertEquals([$profileMock], $this->scheduler->schedule([$profileMock]));
    }

    public function testScheduleHasPayments()
    {
        $profileId = 1;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);

        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentListMock->expects($this->once())
            ->method('hasForProfile')
            ->with($profileId)
            ->willReturn(true);
        $this->generatorMock->expects($this->never())
            ->method('generate');
        $this->persistenceMock->expects($this->never())
            ->method('save');
        $this->engineMock->expects($this->once())
            ->method('processPayments')
            ->with([]);

        $this->assertEquals([$profileMock], $this->scheduler->schedule([$profileMock]));
    }

    public function testScheduleSinglePayment()
    {
        $profileId = 1;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        $generatorSourceMock = $this->createMock(SourceInterface::class);

        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentListMock->expects($this->once())
            ->method('hasForProfile')
            ->with($profileId)
            ->willReturn(false);
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['profile' => $profileMock])
            ->willReturn($generatorSourceMock);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with($generatorSourceMock)
            ->willReturn([]);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::EXPIRED);
        $this->profileResourceMock->expects($this->once())
            ->method('updateStatus')
            ->with($profileMock);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::EXPIRED);
        $this->loggerMock->expects($this->once())
            ->method('traceSchedule')
            ->withConsecutive(
                [
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['profileId' => $profileId],
                    ['profileStatus' => Status::EXPIRED]
                ]
            );
        $this->engineMock->expects($this->once())
            ->method('processPayments')
            ->with([]);

        $this->assertEquals([$profileMock], $this->scheduler->schedule([$profileMock]));
    }

    /**
     * @expectedException \Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException
     */
    public function testSchedulePersistException()
    {
        $profileId = 1;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        $prePaymentInfoMock = $this->createMock(PrePaymentInfoInterface::class);
        $generatorSourceMock = $this->createMock(SourceInterface::class);
        $paymentMock = $this->createMock(Payment::class);

        /** @var Phrase|\PHPUnit_Framework_MockObject_MockObject $exceptionPhraseMock */
        $exception = new CouldNotSaveException(__('Couldn\'t save exception message 1'));

        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentListMock->expects($this->once())
            ->method('hasForProfile')
            ->with($profileId)
            ->willReturn(false);
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['profile' => $profileMock])
            ->willReturn($generatorSourceMock);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with($generatorSourceMock)
            ->willReturn([$paymentMock]);
        $profileMock->expects($this->once())
            ->method('getPrePaymentInfo')
            ->willReturn($prePaymentInfoMock);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsInitialFeePaid')
            ->willReturn(true);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::ACTIVE);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::ACTIVE);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock)
            ->willThrowException($exception);
        $this->persistenceMock->expects($this->never())
            ->method('massDelete');
        $this->loggerMock->expects($this->exactly(2))
            ->method('traceSchedule')
            ->withConsecutive(
                [
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['profileId' => $profileId],
                    ['profileStatus' => Status::ACTIVE]
                ],
                [
                    LoggerInterface::ENTRY_PAYMENTS_SCHEDULE_FAILED,
                    ['profileId' => $profileId],
                    [
                        'exception' => $exception,
                        'payments' => []
                    ]
                ]
            );
        $this->expectException(CouldNotScheduleException::class);
        $this->scheduler->schedule([$profileMock]);
    }

    /**
     * @expectedException \Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException
     */
    public function testScheduleProcessException()
    {
        $profileId = 1;
        $paymentId = 2;
        $scheduleId = 3;

        /** @var ProfileInterface|\PHPUnit_Framework_MockObject_MockObject $profileMock */
        $profileMock = $this->createMock(ProfileInterface::class);
        $prePaymentInfoMock = $this->createMock(PrePaymentInfoInterface::class);
        $generatorSourceMock = $this->createMock(SourceInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $exception = new CouldNotSaveException(__('Couldn\'t save exception message 2'));

        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $this->paymentListMock->expects($this->once())
            ->method('hasForProfile')
            ->with($profileId)
            ->willReturn(false);
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['profile' => $profileMock])
            ->willReturn($generatorSourceMock);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with($generatorSourceMock)
            ->willReturn([$paymentMock]);
        $profileMock->expects($this->once())
            ->method('getPrePaymentInfo')
            ->willReturn($prePaymentInfoMock);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsInitialFeePaid')
            ->willReturn(true);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::ACTIVE);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn(Status::ACTIVE);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $paymentMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $this->notificationManagerMock->expects($this->exactly(2))
            ->method('schedule')
            ->withConsecutive(
                [
                    NotificationInterface::TYPE_SUBSCRIPTION_CREATED,
                    $paymentMock
                ],
                [
                    NotificationInterface::TYPE_UPCOMING_BILLING,
                    $paymentMock
                ]
            );
        $this->engineMock->expects($this->once())
            ->method('processPayments')
            ->with([$paymentId])
            ->willThrowException($exception);
        $this->persistenceMock->expects($this->once())
            ->method('massDelete')
            ->with([$paymentMock]);
        $this->loggerMock->expects($this->exactly(3))
            ->method('traceSchedule')
            ->withConsecutive(
                [
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['profileId' => $profileId],
                    ['profileStatus' => Status::ACTIVE]
                ],
                [
                    LoggerInterface::ENTRY_PAYMENTS_SCHEDULED,
                    ['profileId' => $profileId],
                    ['payments' => [$paymentMock]]
                ],
                [
                    LoggerInterface::ENTRY_PAYMENTS_SCHEDULE_FAILED,
                    ['profileId' => $profileId],
                    [
                        'exception' => $exception,
                        'payments' => [$paymentMock]
                    ]
                ]
            );
        $this->expectException(CouldNotScheduleException::class);
        $this->scheduler->schedule([$profileMock]);
    }
}
