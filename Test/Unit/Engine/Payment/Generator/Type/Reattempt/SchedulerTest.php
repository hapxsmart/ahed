<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Generator\Type\Reattempt;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\DataResolver\NextReattemptDate;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\ScheduleResult;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\ScheduleResultFactory;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler
 */
class SchedulerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scheduler
     */
    private $scheduler;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    /**
     * @var NextReattemptDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextReattemptDateMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    /**
     * @var CoreDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreDateMock;

    /**
     * @var ScheduleResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    /**
     * @var ValueResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulePeriodValueResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->nextReattemptDateMock = $this->createMock(NextReattemptDate::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->coreDateMock = $this->createMock(CoreDate::class);
        $this->resultFactoryMock = $this->createMock(ScheduleResultFactory::class);
        $this->schedulePeriodValueResolverMock = $this->createMock(ValueResolver::class);
        $this->scheduler = $objectManager->getObject(
            Scheduler::class,
            [
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'nextReattemptDate' => $this->nextReattemptDateMock,
                'dateTime' => $this->dateTimeMock,
                'coreDate' => $this->coreDateMock,
                'resultFactory' => $this->resultFactoryMock,
                'periodValueResolver' => $this->schedulePeriodValueResolverMock
            ]
        );
    }

    /**
     * @param string $today
     * @param string $nextPaymentDate
     * @param string $nextRetryDate
     * @param string $lastRetryDate
     * @param bool $isBundled
     * @param array $resultData
     * @dataProvider scheduleDataProvider
     */
    public function testSchedule(
        $today,
        $nextPaymentDate,
        $nextRetryDate,
        $lastRetryDate,
        $isBundled,
        $resultData
    ) {
        $period = BillingPeriod::DAY;
        $frequency = 1;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);
        $resultMock = $this->createMock(ScheduleResult::class);

        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($today);
        $paymentMock->expects($this->once())
            ->method('getScheduledAt')
            ->willReturn($today);
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
            ->method('getDateNext')
            ->willReturn($nextPaymentDate);
        $this->nextReattemptDateMock->expects($this->once())
            ->method('getLastDate')
            ->with($today)
            ->willReturn($lastRetryDate);
        $this->coreDateMock->expects($this->exactly(2))
            ->method('gmtTimestamp')
            ->willReturnCallback(
                function ($input) {
                    return (new \DateTime($input))->getTimestamp();
                }
            );
        $paymentMock->expects($this->any())
            ->method('isBundled')
            ->willReturn($isBundled);
        $this->nextReattemptDateMock->expects($this->any())
            ->method('getDateNext')
            ->with($today)
            ->willReturn($nextRetryDate);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with($resultData)
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->scheduler->schedule($paymentMock));
    }

    /**
     * @return array
     */
    public function scheduleDataProvider()
    {
        return [
            [
                '2018-09-01 12:00:00',
                '2018-09-02 12:00:00',
                '2018-09-04 12:00:00',
                '2018-09-09 12:00:00',
                false,
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_NEXT,
                    'date' => '2018-09-04 12:00:00'
                ]
            ],
            [
                '2018-09-01 12:00:00',
                '2018-09-10 12:00:00',
                '2018-09-04 12:00:00',
                '2018-09-09 12:00:00',
                false,
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_RETRY,
                    'date' => '2018-09-04 12:00:00'
                ]
            ],
            [
                '2018-09-01 12:00:00',
                '2018-09-10 12:00:00',
                '2018-09-04 12:00:00',
                '2018-09-09 12:00:00',
                true,
                [
                    'type' => ScheduleResult::REATTEMPT_TYPE_NEXT,
                    'date' => '2018-09-04 12:00:00'
                ]
            ]
        ];
    }
}
