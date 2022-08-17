<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Generator\Type\Next
 */
class NextTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Next
     */
    private $generator;

    /**
     * @var Evaluation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $evaluationMock;

    /**
     * @var NextPaymentDate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $nextPaymentDateMock;

    /**
     * @var PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFactoryMock;

    /**
     * @var ValueResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $schedulePeriodValueResolverMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->evaluationMock = $this->createMock(Evaluation::class);
        $this->nextPaymentDateMock = $this->createMock(NextPaymentDate::class);
        $this->paymentFactoryMock = $this->createMock(PaymentFactory::class);
        $this->schedulePeriodValueResolverMock = $this->createMock(ValueResolver::class);
        $this->generator = $objectManager->getObject(
            Next::class,
            [
                'evaluation' => $this->evaluationMock,
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'paymentFactory' => $this->paymentFactoryMock,
                'periodValueResolver' => $this->schedulePeriodValueResolverMock
            ]
        );
    }

    public function testGenerate()
    {
        $period = BillingPeriod::DAY;
        $frequency = 1;
        $profileId = 2;
        $tokenId = 3;
        $scheduleId = 4;
        $paidAt = '2018-09-01 12:00:00';
        $nextDate = '2018-09-02 12:00:00';
        $profileStatus = Status::ACTIVE;
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;
        $paymentType = PaymentInterface::TYPE_PLANNED;
        $totalAmount = 10.00;
        $baseTotalAmount = 15.00;

        /** @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->createMock(SourceInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $paymentDetailsMock = $this->createMock(PaymentDetails::class);
        $sourcePaymentMock = $this->createMock(Payment::class);
        $paymentMock = $this->createMock(Payment::class);

        $sourceMock->expects($this->once())
            ->method('getPayments')
            ->willReturn([$sourcePaymentMock]);
        $sourcePaymentMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $profileMock->expects($this->once())
            ->method('getStatus')
            ->willReturn($profileStatus);
        $sourcePaymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $sourcePaymentMock->expects($this->once())
            ->method('getPaidAt')
            ->willReturn($paidAt);
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
            ->willReturn($nextDate);
        $this->evaluationMock->expects($this->once())
            ->method('evaluate')
            ->with($scheduleMock, $profileMock, $nextDate, $paidAt)
            ->willReturn([$paymentDetailsMock]);
        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentMock);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $profileMock->expects($this->once())
            ->method('getPaymentTokenId')
            ->willReturn($tokenId);
        $paymentDetailsMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn($paymentPeriod);
        $paymentDetailsMock->expects($this->once())
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $paymentDetailsMock->expects($this->once())
            ->method('getDate')
            ->willReturn($nextDate);
        $paymentDetailsMock->expects($this->once())
            ->method('getTotalAmount')
            ->willReturn($totalAmount);
        $paymentDetailsMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->willReturn($baseTotalAmount);
        $scheduleMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);
        $paymentMock->expects($this->once())
            ->method('setProfileId')
            ->with($profileId)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setType')
            ->with($paymentType)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setPaymentPeriod')
            ->with($paymentPeriod)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PLANNED)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setScheduledAt')
            ->with($nextDate)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setPaymentData')
            ->with(['token_id' => $tokenId])
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setTotalScheduled')
            ->with($totalAmount)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setBaseTotalScheduled')
            ->with($baseTotalAmount)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setSchedule')
            ->with($scheduleMock)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setScheduleId')
            ->with($scheduleId)
            ->willReturnSelf();

        $this->assertEquals([$paymentMock], $this->generator->generate($sourceMock));
    }
}
