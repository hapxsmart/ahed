<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Failure\Handler\Single;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Failure\HandlerInterface;
use Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Single\DefaultHandler;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\ScheduleResult;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Failure\Handler\Single\DefaultHandler
 */
class DefaultHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultHandler
     */
    private $handler;

    /**
     * @var Reattempt|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorMock;

    /**
     * @var SourceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $generatorSourceFactoryMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var Incrementor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateIncrementorMock;

    /**
     * @var Scheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reattemptSchedulerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->generatorMock = $this->createMock(Reattempt::class);
        $this->generatorSourceFactoryMock = $this->createMock(SourceFactory::class);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->stateIncrementorMock = $this->createMock(Incrementor::class);
        $this->reattemptSchedulerMock = $this->createMock(Scheduler::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->handler = $objectManager->getObject(
            DefaultHandler::class,
            [
                'generator' => $this->generatorMock,
                'generatorSourceFactory' => $this->generatorSourceFactoryMock,
                'persistence' => $this->persistenceMock,
                'stateIncrementor' => $this->stateIncrementorMock,
                'reattemptScheduler' => $this->reattemptSchedulerMock,
                'logger' => $this->loggerMock,
                'config' => $this->configMock
            ]
        );
    }

    public function testHandle()
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $reattemptMock = $this->createMock(Payment::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $generatorSourceMock = $this->createMock(Source::class);

        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_UNPROCESSABLE);
        $paymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::SUSPENDED);
        $this->generatorSourceFactoryMock->expects($this->once())
            ->method('create')
            ->with(['payments' => [$paymentMock]])
            ->willReturn($generatorSourceMock);
        $this->generatorMock->expects($this->once())
            ->method('generate')
            ->with($generatorSourceMock)
            ->willReturn([$reattemptMock]);
        $this->persistenceMock->expects($this->exactly(2))
            ->method('save')
            ->withConsecutive([$reattemptMock], [$paymentMock]);
        $this->loggerMock->expects($this->exactly(3))
            ->method('traceProcessing')
            ->withConsecutive(
                [
                    LoggerInterface::ENTRY_PAYMENT_STATUS_CHANGE,
                    ['payment' => $paymentMock]
                ],
                [
                    LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                    ['payment' => $paymentMock],
                    ['profile' => $profileMock]
                ],
                [
                    LoggerInterface::ENTRY_PAYMENT_REATTEMPT_CREATED,
                    ['payment' => $paymentMock],
                    ['reattempt' => $reattemptMock]
                ]
            );

        $this->assertSame($paymentMock, $this->handler->handle($paymentMock));
    }

    public function testHandleReattempt()
    {
        $retriesCount = 1;
        $maxRetriesCount = 3;
        $reattemptDate = '2018-09-13 08:31:28';

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleResultMock = $this->createMock(ScheduleResult::class);

        $paymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $paymentMock->expects($this->once())
            ->method('getRetriesCount')
            ->willReturn($retriesCount);
        $this->configMock->expects($this->once())
            ->method('getMaxRetriesCount')
            ->willReturn($maxRetriesCount);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::SUSPENDED);
        $this->reattemptSchedulerMock->expects($this->once())
            ->method('schedule')
            ->with($paymentMock)
            ->willReturn($scheduleResultMock);
        $scheduleResultMock->expects($this->once())
            ->method('getDate')
            ->willReturn($reattemptDate);
        $paymentMock->expects($this->once())
            ->method('setRetryAt')
            ->with($reattemptDate);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PAYMENT_REATTEMPT_RESCHEDULED,
                ['payment' => $paymentMock],
                ['date' => $reattemptDate]
            );
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with($retriesCount + 1);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);

        $this->handler->handleReattempt($paymentMock);
    }

    public function testHandleReattemptMaxPaymentsExceeded()
    {
        $retriesCount = 3;
        $maxRetriesCount = 3;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $profileMock = $this->createMock(ProfileInterface::class);

        $paymentMock->expects($this->once())
            ->method('getRetriesCount')
            ->willReturn($retriesCount);
        $this->configMock->expects($this->once())
            ->method('getMaxRetriesCount')
            ->willReturn($maxRetriesCount);
        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_FAILED);
        $paymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $profileMock->expects($this->once())
            ->method('setStatus')
            ->with(Status::CANCELLED);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                ['payment' => $paymentMock],
                ['profile' => $profileMock]
            );
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with($retriesCount + 1);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);

        $this->handler->handleReattempt($paymentMock);
    }
}
