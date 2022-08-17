<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\Scheduler;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt\ScheduleResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Generator\Type\Reattempt
 */
class ReattemptTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Reattempt
     */
    private $generator;

    /**
     * @var Evaluation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $evaluationMock;

    /**
     * @var Scheduler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulerMock;

    /**
     * @var Incrementor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateIncrementorMock;

    /**
     * @var PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFactoryMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->evaluationMock = $this->createMock(Evaluation::class);
        $this->schedulerMock = $this->createMock(Scheduler::class);
        $this->stateIncrementorMock = $this->createMock(Incrementor::class);
        $this->paymentFactoryMock = $this->createMock(PaymentFactory::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->generator = $objectManager->getObject(
            Reattempt::class,
            [
                'evaluation' => $this->evaluationMock,
                'scheduler' => $this->schedulerMock,
                'stateIncrementor' => $this->stateIncrementorMock,
                'paymentFactory' => $this->paymentFactoryMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    /**
     * @param bool $isBundled
     * @dataProvider isBundledDataProvider
     */
    public function testGenerateRetry($isBundled)
    {
        $profileId = 1;
        $tokenId = 2;
        $scheduleId = 3;
        $scheduledAt = '2018-09-01 12:00:00';
        $reattemptDate = '2018-09-02 12:00:00';
        $totalAmount = 10.00;
        $baseTotalAmount = 15.00;
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;

        /** @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->createMock(SourceInterface::class);
        $sourcePaymentMock = $this->createMock(Payment::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleResultMock = $this->createMock(ScheduleResult::class);
        $reattemptMock = $this->createMock(Payment::class);

        $sourceMock->expects($this->once())
            ->method('getPayments')
            ->willReturn([$sourcePaymentMock]);
        $sourcePaymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $sourcePaymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $sourcePaymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn($isBundled);
        $this->schedulerMock->expects($this->once())
            ->method('schedule')
            ->with($sourcePaymentMock)
            ->willReturn($scheduleResultMock);
        $scheduleResultMock->expects($this->once())
            ->method('getType')
            ->willReturn(ScheduleResult::REATTEMPT_TYPE_RETRY);
        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($reattemptMock);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $sourcePaymentMock->expects($this->once())
            ->method('getScheduledAt')
            ->willReturn($scheduledAt);
        $scheduleResultMock->expects($this->once())
            ->method('getDate')
            ->willReturn($reattemptDate);
        $profileMock->expects($this->once())
            ->method('getPaymentTokenId')
            ->willReturn($tokenId);
        $sourcePaymentMock->expects($this->once())
            ->method('getTotalScheduled')
            ->willReturn($totalAmount);
        $sourcePaymentMock->expects($this->once())
            ->method('getBaseTotalScheduled')
            ->willReturn($baseTotalAmount);
        $scheduleMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);
        $reattemptMock->expects($this->once())
            ->method('setProfileId')
            ->with($profileId)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setType')
            ->with(PaymentInterface::TYPE_REATTEMPT)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PENDING)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setScheduledAt')
            ->with($scheduledAt)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setRetryAt')
            ->with($reattemptDate)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setPaymentData')
            ->with(['token_id' => $tokenId])
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setTotalScheduled')
            ->with($totalAmount)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setBaseTotalScheduled')
            ->with($baseTotalAmount)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setSchedule')
            ->with($scheduleMock)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setScheduleId')
            ->with($scheduleId)
            ->willReturnSelf();

        if (!$isBundled) {
            $sourcePaymentMock->expects($this->once())
                ->method('getPaymentPeriod')
                ->willReturn($paymentPeriod);
            $reattemptMock->expects($this->once())
                ->method('setPaymentPeriod')
                ->with($paymentPeriod)
                ->willReturnSelf();
        }

        $this->assertEquals([$reattemptMock], $this->generator->generate($sourceMock));
    }

    /**
     * @param bool $isBundled
     * @dataProvider isBundledDataProvider
     */
    public function testGenerateNext($isBundled)
    {
        $profileId = 1;
        $tokenId = 2;
        $scheduleId = 3;
        $today = '2018-09-01 12:00:00';
        $reattemptDate = '2018-09-02 12:00:00';
        $totalAmount = 10.00;
        $baseTotalAmount = 15.00;
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;

        /** @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->createMock(SourceInterface::class);
        $sourcePaymentMock = $this->createMock(Payment::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $scheduleResultMock = $this->createMock(ScheduleResult::class);
        $paymentDetailsMock = $this->createMock(PaymentDetails::class);
        $reattemptMock = $this->createMock(Payment::class);

        $sourceMock->expects($this->once())
            ->method('getPayments')
            ->willReturn([$sourcePaymentMock]);
        $sourcePaymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $sourcePaymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $sourcePaymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn($isBundled);
        $this->schedulerMock->expects($this->once())
            ->method('schedule')
            ->with($sourcePaymentMock)
            ->willReturn($scheduleResultMock);
        $scheduleResultMock->expects($this->once())
            ->method('getType')
            ->willReturn(ScheduleResult::REATTEMPT_TYPE_NEXT);
        $scheduleResultMock->expects($this->once())
            ->method('getDate')
            ->willReturn($reattemptDate);
        $this->evaluationMock->expects($this->once())
            ->method('evaluate')
            ->with(
                $scheduleMock,
                $profileMock,
                $reattemptDate
            )
            ->willReturn([$paymentDetailsMock]);
        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($reattemptMock);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $profileMock->expects($this->once())
            ->method('getPaymentTokenId')
            ->willReturn($tokenId);
        $sourcePaymentMock->expects($this->once())
            ->method('getScheduledAt')
            ->willReturn($today);
        $paymentDetailsMock->expects($this->once())
            ->method('getDate')
            ->willReturn($reattemptDate);
        $scheduleMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);
        $reattemptMock->expects($this->once())
            ->method('setProfileId')
            ->with($profileId)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setType')
            ->with(PaymentInterface::TYPE_REATTEMPT)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PENDING)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setScheduledAt')
            ->with($today)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setRetryAt')
            ->with($reattemptDate)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setPaymentData')
            ->with(['token_id' => $tokenId])
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setTotalScheduled')
            ->with($totalAmount)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setBaseTotalScheduled')
            ->with($baseTotalAmount)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setSchedule')
            ->with($scheduleMock)
            ->willReturnSelf();
        $reattemptMock->expects($this->once())
            ->method('setScheduleId')
            ->with($scheduleId)
            ->willReturnSelf();

        if ($isBundled) {
            $sourcePaymentMock->expects($this->once())
                ->method('getTotalScheduled')
                ->willReturn($totalAmount);
            $sourcePaymentMock->expects($this->once())
                ->method('getBaseTotalScheduled')
                ->willReturn($baseTotalAmount);
        } else {
            $paymentDetailsMock->expects($this->once())
                ->method('getTotalAmount')
                ->willReturn($totalAmount);
            $paymentDetailsMock->expects($this->once())
                ->method('getBaseTotalAmount')
                ->willReturn($baseTotalAmount);
            $paymentDetailsMock->expects($this->once())
                ->method('getPaymentPeriod')
                ->willReturn($paymentPeriod);
            $reattemptMock->expects($this->once())
                ->method('setPaymentPeriod')
                ->with($paymentPeriod)
                ->willReturnSelf();
        }

        $this->assertEquals([$reattemptMock], $this->generator->generate($sourceMock));
    }

    /**
     * @return array
     */
    public function isBundledDataProvider()
    {
        return [[false], [true]];
    }
}
