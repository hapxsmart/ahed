<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Evaluation\PaymentDetails;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\Initial;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Generator\Type\Initial
 */
class InitialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Billing period
     */
    const BILLING_PERIOD = BillingPeriod::DAY;

    /**
     * Billing frequency
     */
    const BILLING_FREQUENCY = 1;

    /**
     * Trial total billing cycles
     */
    const TRIAL_TOTAL_BILLING_CYCLES = 2;

    /**
     * Total billing cycles
     */
    const TOTAL_BILLING_CYCLES = 10;

    /**
     * Membership model flag
     */
    const IS_MEMBERSHIP_MODEL = 0;

    /**
     * @var Initial
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
     * @var ScheduleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleFactoryMock;

    /**
     * @var DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dateTimeMock;

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
        $this->scheduleFactoryMock = $this->createMock(ScheduleFactory::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);
        $this->schedulePeriodValueResolverMock = $this->createMock(ValueResolver::class);
        $this->generator = $objectManager->getObject(
            Initial::class,
            [
                'evaluation' => $this->evaluationMock,
                'nextPaymentDate' => $this->nextPaymentDateMock,
                'paymentFactory' => $this->paymentFactoryMock,
                'scheduleFactory' => $this->scheduleFactoryMock,
                'dateTime' => $this->dateTimeMock,
                'schedulePeriodValueResolver' => $this->schedulePeriodValueResolverMock
            ]
        );
    }

    /**
     * @param PlanDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject $planDefinitionMock
     * @param bool $isTrialPeriodEnabled
     * @param bool $isInitialPaid
     * @param bool $isTrialPaid
     * @param bool $isRegularPaid
     * @dataProvider generateWithPrePaymentsDataProvider
     */
    public function testGenerateWithPrePayments(
        $planDefinitionMock,
        $isTrialPeriodEnabled,
        $isInitialPaid,
        $isTrialPaid,
        $isRegularPaid
    ) {
        $storeId = 3;
        $profileId = 4;
        $tokenId = 5;
        $today = '2018-09-01 12:00:00';
        $yesterday = '2018-09-02 12:00:00';
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;
        $totalAmount = 10.00;
        $baseTotalAmount = 15.00;
        $paymentType = PaymentInterface::TYPE_PLANNED;

        /** @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->createMock(SourceInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $prePaymentInfoMock = $this->createMock(PrePaymentInfoInterface::class);
        $paymentDetailsMock = $this->createMock(PaymentDetails::class);
        $paymentMock = $this->createMock(Payment::class);

        $sourceMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($scheduleMock);
        $profileMock->expects($this->once())
            ->method('getProfileDefinition')
            ->willReturn($planDefinitionMock);
        $profileMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $scheduleMock->expects($this->once())
            ->method('setPeriod')
            ->with(self::BILLING_PERIOD)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setFrequency')
            ->with(self::BILLING_FREQUENCY)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setTrialPeriod')
            ->with(self::BILLING_PERIOD)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setTrialFrequency')
            ->with(self::BILLING_FREQUENCY)
            ->willReturnSelf();
        if ($isTrialPeriodEnabled) {
            $scheduleMock->expects($this->once())
                ->method('setTrialTotalCount')
                ->with(self::TRIAL_TOTAL_BILLING_CYCLES)
                ->willReturnSelf();
        } else {
            $scheduleMock->expects($this->once())
                ->method('setTrialTotalCount')
                ->with(0)
                ->willReturnSelf();
        }
        $scheduleMock->expects($this->once())
            ->method('setRegularTotalCount')
            ->with(self::TOTAL_BILLING_CYCLES)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setIsMembershipModel')
            ->willReturnSelf();
        $profileMock->expects($this->once())
            ->method('getPrePaymentInfo')
            ->willReturn($prePaymentInfoMock);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsInitialFeePaid')
            ->willReturn($isInitialPaid);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsTrialPaid')
            ->willReturn($isTrialPaid);
        $prePaymentInfoMock->expects($this->once())
            ->method('getIsRegularPaid')
            ->willReturn($isRegularPaid);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($today);
        $scheduleMock->expects($this->once())
            ->method('setIsInitialPaid')
            ->with($isInitialPaid)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setTrialCount')
            ->with($isTrialPaid ? 1 : 0)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setRegularCount')
            ->with($isRegularPaid ? 1 : 0)
            ->willReturnSelf();
        $this->schedulePeriodValueResolverMock->expects($this->once())
            ->method('getPeriod')
            ->with($scheduleMock)
            ->willReturn(self::BILLING_PERIOD);
        $this->schedulePeriodValueResolverMock->expects($this->once())
            ->method('getFrequency')
            ->with($scheduleMock)
            ->willReturn(self::BILLING_FREQUENCY);
        $this->nextPaymentDateMock->expects($this->once())
            ->method('getDateNext')
            ->with($today, self::BILLING_PERIOD, self::BILLING_FREQUENCY)
            ->willReturn($yesterday);
        $this->evaluationMock->expects($this->once())
            ->method('evaluate')
            ->with(
                $scheduleMock,
                $profileMock,
                $yesterday,
                $today
            )
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
            ->method('getDate')
            ->willReturn($yesterday);
        $paymentDetailsMock->expects($this->once())
            ->method('getTotalAmount')
            ->willReturn($totalAmount);
        $paymentDetailsMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->willReturn($baseTotalAmount);
        $paymentMock->expects($this->once())
            ->method('setProfileId')
            ->with($profileId)
            ->willReturnSelf();
        $paymentMock->expects($this->once())
            ->method('setProfile')
            ->with($profileMock)
            ->willReturnSelf();
        $paymentDetailsMock->expects($this->once())
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $paymentMock->expects($this->once())
            ->method('setType')
            ->with(PaymentInterface::TYPE_PLANNED)
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
            ->with($yesterday)
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

        $this->assertEquals([$paymentMock], $this->generator->generate($sourceMock));
    }

    /**
     * @param PlanDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject $planDefinitionMock
     * @param bool $isTrialPeriodEnabled
     * @param PrePaymentInfoInterface|\PHPUnit_Framework_MockObject_MockObject|null $prePaymentsInfoMock
     * @dataProvider generateWithoutPrePaymentsDataProvider
     */
    public function testGenerateWithoutPrePayments(
        $planDefinitionMock,
        $isTrialPeriodEnabled,
        $prePaymentsInfoMock
    ) {
        $storeId = 3;
        $profileId = 4;
        $tokenId = 5;
        $today = '2018-09-01 12:00:00';
        $yesterday = '2018-09-02 12:00:00';
        $paymentPeriod = PaymentInterface::PERIOD_REGULAR;
        $totalAmount = 10.00;
        $baseTotalAmount = 15.00;
        $paymentType = PaymentInterface::TYPE_PLANNED;

        /** @var SourceInterface|\PHPUnit_Framework_MockObject_MockObject $sourceMock */
        $sourceMock = $this->createMock(SourceInterface::class);
        $profileMock = $this->createMock(ProfileInterface::class);
        $scheduleMock = $this->createMock(Schedule::class);
        $paymentDetailsMock = $this->createMock(PaymentDetails::class);
        $paymentMock = $this->createMock(Payment::class);

        $sourceMock->expects($this->once())
            ->method('getProfile')
            ->willReturn($profileMock);
        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($scheduleMock);
        $profileMock->expects($this->once())
            ->method('getProfileDefinition')
            ->willReturn($planDefinitionMock);
        $profileMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $scheduleMock->expects($this->once())
            ->method('setPeriod')
            ->with(self::BILLING_PERIOD)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setFrequency')
            ->with(self::BILLING_FREQUENCY)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setTrialPeriod')
            ->with(self::BILLING_PERIOD)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setTrialFrequency')
            ->with(self::BILLING_FREQUENCY)
            ->willReturnSelf();
        if ($isTrialPeriodEnabled) {
            $scheduleMock->expects($this->once())
                ->method('setTrialTotalCount')
                ->with(self::TRIAL_TOTAL_BILLING_CYCLES)
                ->willReturnSelf();
        } else {
            $scheduleMock->expects($this->once())
                ->method('setTrialTotalCount')
                ->with(0)
                ->willReturnSelf();
        }
        $scheduleMock->expects($this->once())
            ->method('setRegularTotalCount')
            ->with(self::TOTAL_BILLING_CYCLES)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnSelf();
        $scheduleMock->expects($this->once())
            ->method('setIsMembershipModel')
            ->willReturnSelf();
        $profileMock->expects($this->once())
            ->method('getPrePaymentInfo')
            ->willReturn($prePaymentsInfoMock);
        $this->dateTimeMock->expects($this->once())
            ->method('formatDate')
            ->with(true)
            ->willReturn($today);
        $this->evaluationMock->expects($this->once())
            ->method('evaluate')
            ->with(
                $scheduleMock,
                $profileMock,
                $today
            )
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
            ->method('getDate')
            ->willReturn($yesterday);
        $paymentDetailsMock->expects($this->once())
            ->method('getTotalAmount')
            ->willReturn($totalAmount);
        $paymentDetailsMock->expects($this->once())
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $paymentDetailsMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->willReturn($baseTotalAmount);
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
            ->with(PaymentInterface::TYPE_PLANNED)
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
            ->with($yesterday)
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

        $this->assertEquals([$paymentMock], $this->generator->generate($sourceMock));
    }

    /**
     * @return array
     */
    public function generateWithPrePaymentsDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => true,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES,
                        'getIsMembershipModelEnabled' => self::IS_MEMBERSHIP_MODEL
                    ]
                ),
                true,
                true,
                false,
                false
            ],
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => true,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                true,
                false,
                true,
                false
            ],
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => true,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                true,
                false,
                false,
                true
            ],
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => false,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                false,
                true,
                false,
                false
            ]
        ];
    }

    /**
     * @return array
     */
    public function generateWithoutPrePaymentsDataProvider()
    {
        return [
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => true,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                true,
                null
            ],
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => false,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                false,
                null
            ],
            [
                $this->createConfiguredMock(
                    PlanDefinitionInterface::class,
                    [
                        'getBillingPeriod' => self::BILLING_PERIOD,
                        'getBillingFrequency' => self::BILLING_FREQUENCY,
                        'getTrialBillingPeriod' => self::BILLING_PERIOD,
                        'getTrialBillingFrequency' => self::BILLING_FREQUENCY,
                        'getIsTrialPeriodEnabled' => true,
                        'getTrialTotalBillingCycles' => self::TRIAL_TOTAL_BILLING_CYCLES,
                        'getTotalBillingCycles' => self::TOTAL_BILLING_CYCLES
                    ]
                ),
                true,
                $this->createConfiguredMock(
                    PrePaymentInfoInterface::class,
                    [
                        'getIsInitialFeePaid' => false,
                        'getIsTrialPaid' => false,
                        'getIsRegularPaid' => false
                    ]
                )
            ]
        ];
    }
}
