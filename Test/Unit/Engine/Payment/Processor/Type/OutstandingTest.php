<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\Result;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\PaymentType\Resolver as PaymentTypeResolver;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\Reason\Resolver as ReasonResolver;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding
 */
class OutstandingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Outstanding
     */
    private $processor;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var IsProcessable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isProcessableCheckerMock;

    /**
     * @var ReasonResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $reasonResolverMock;

    /**
     * @var PaymentTypeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentTypeResolverMock;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ValueResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulePeriodValueResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->isProcessableCheckerMock = $this->createMock(IsProcessable::class);
        $this->reasonResolverMock = $this->createMock(ReasonResolver::class);
        $this->paymentTypeResolverMock = $this->createMock(PaymentTypeResolver::class);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->schedulePeriodValueResolverMock = $this->createMock(ValueResolver::class);
        $this->processor = $objectManager->getObject(
            Outstanding::class,
            [
                'persistence' => $this->persistenceMock,
                'isProcessableChecker' => $this->isProcessableCheckerMock,
                'reasonResolver' => $this->reasonResolverMock,
                'paymentTypeResolver' => $this->paymentTypeResolverMock,
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'dateTime' => $this->dateTimeMock,
                'resultFactory' => $this->resultFactoryMock,
                'logger' => $this->loggerMock,
                'periodValueResolver' => $this->schedulePeriodValueResolverMock
            ]
        );
    }

    public function testProcessNotProcessable()
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $processResultMock = $this->createMock(Result::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_OUTSTANDING)
            ->willReturn(false);
        $this->persistenceMock->expects($this->never())
            ->method('save')
            ->with($paymentMock);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @param string $date
     * @param string $today
     * @param string $nextDateForOutstanding
     * @param string $paymentType
     * @param string $paymentStatus
     * @param int $reason
     * @param string $expectedRescheduleDate
     * @dataProvider processDataProvider
     */
    public function testProcess(
        $date,
        $today,
        $nextDateForOutstanding,
        $paymentType,
        $paymentStatus,
        $reason,
        $expectedRescheduleDate
    ) {
        $period = BillingPeriod::DAY;
        $frequency = 2;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);
        $processResultMock = $this->createMock(Result::class);

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_OUTSTANDING)
            ->willReturn(true);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $paymentMock->expects($this->once())
            ->method('getPaymentStatus')
            ->willReturn($paymentStatus);
        $this->reasonResolverMock->expects($this->once())
            ->method('getReason')
            ->with($paymentMock)
            ->willReturn($reason);
        $paymentMock->expects($this->once())
            ->method(
                $paymentStatus == PaymentInterface::STATUS_RETRYING
                    ? 'getRetryAt'
                    : 'getScheduledAt'
            )
            ->willReturn($date);
        if ($reason == ReasonResolver::REASON_REACTIVATED) {
            $scheduleMock->expects($this->any())
                ->method('getPeriod')
                ->willReturn($period);
            $scheduleMock->expects($this->any())
                ->method('getFrequency')
                ->willReturn($frequency);
            $this->schedulePeriodValueResolverMock->expects($this->once())
                ->method('getPeriod')
                ->willReturn($period);
            $this->schedulePeriodValueResolverMock->expects($this->once())
                ->method('getFrequency')
                ->willReturn($frequency);
            $this->nextPaymentDateMock->expects($this->once())
                ->method('getDateNextForOutstanding')
                ->willReturn($nextDateForOutstanding);
            $scheduleMock->expects($this->once())
                ->method('setIsReactivated')
                ->with(false);
        } else {
            $this->dateTimeMock->expects($this->once())
                ->method('formatDate')
                ->with(true)
                ->willReturn($today);
        }
        $this->paymentTypeResolverMock->expects($this->once())
            ->method('getPaymentType')
            ->with($paymentMock)
            ->willReturn($paymentType);
        $paymentMock->expects($this->once())
            ->method('setType')
            ->with($paymentType);
        $paymentMock->expects($this->once())
            ->method(
                $paymentStatus == PaymentInterface::STATUS_RETRYING
                    ? 'setRetryAt'
                    : 'setScheduledAt'
            )
            ->with($expectedRescheduleDate);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PAYMENT_UPDATE,
                ['payments' => [$paymentMock]],
                ['updatedPayment' => $paymentMock]
            );
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    public function testProcessException()
    {
        $scheduledAt = '2018-08-01 12:00:00';
        $today = '2018-08-02 12:00:00';
        $paymentType = PaymentInterface::TYPE_PLANNED;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);
        $processResultMock = $this->createMock(Result::class);
        $exception = new \Exception('Persistence error.');

        $this->isProcessableCheckerMock->expects($this->once())
            ->method('check')
            ->with($paymentMock, PaymentInterface::TYPE_OUTSTANDING)
            ->willReturn(true);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $paymentMock->expects($this->once())
            ->method('getPaymentStatus')
            ->willReturn(PaymentInterface::STATUS_PLANNED);
        $paymentMock->expects($this->once())
            ->method('getScheduledAt')
            ->willReturn($scheduledAt);
        $this->reasonResolverMock->expects($this->once())
            ->method('getReason')
            ->with($paymentMock)
            ->willReturn(ReasonResolver::REASON_CYCLE_MISSING);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($today);
        $this->paymentTypeResolverMock->expects($this->once())
            ->method('getPaymentType')
            ->with($paymentMock)
            ->willReturn($paymentType);
        $paymentMock->expects($this->once())
            ->method('setType')
            ->with($paymentType);
        $paymentMock->expects($this->once())
            ->method('setScheduledAt')
            ->with($today);
        $this->persistenceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock)
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PAYMENT_UPDATE_FAILED,
                ['payments' => [$paymentMock]],
                [
                    'updatedPayment' => $paymentMock,
                    'exception' => $exception
                ]
            );
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($processResultMock);

        $this->assertSame($processResultMock, $this->processor->process([$paymentMock]));
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                '2018-08-01 12:00:00',
                '2018-08-02 12:00:00',
                '2018-08-03 12:00:00',
                PaymentInterface::TYPE_PLANNED,
                PaymentInterface::STATUS_PLANNED,
                ReasonResolver::REASON_CYCLE_MISSING,
                '2018-08-02 12:00:00'
            ],
            [
                '2018-08-01 12:00:00',
                '2018-08-02 12:00:00',
                '2018-08-03 12:00:00',
                PaymentInterface::TYPE_PLANNED,
                PaymentInterface::STATUS_PLANNED,
                ReasonResolver::REASON_REACTIVATED,
                '2018-08-03 12:00:00'
            ],
            [
                '2018-08-01 12:00:00',
                '2018-08-02 12:00:00',
                '2018-08-03 12:00:00',
                PaymentInterface::TYPE_REATTEMPT,
                PaymentInterface::STATUS_RETRYING,
                ReasonResolver::REASON_CYCLE_MISSING,
                '2018-08-02 12:00:00'
            ],
            [
                '2018-08-01 12:00:00',
                '2018-08-02 12:00:00',
                '2018-08-03 12:00:00',
                PaymentInterface::TYPE_REATTEMPT,
                PaymentInterface::STATUS_RETRYING,
                ReasonResolver::REASON_REACTIVATED,
                '2018-08-03 12:00:00'
            ]
        ];
    }
}
