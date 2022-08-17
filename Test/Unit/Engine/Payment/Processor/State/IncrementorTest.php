<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\State;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile\StatusHandler;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\State\Incrementor
 */
class IncrementorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Incrementor
     */
    private $incrementor;

    /**
     * @var StatusHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileStatusHandlerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->profileStatusHandlerMock = $this->createMock(StatusHandler::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->incrementor = $objectManager->getObject(
            Incrementor::class,
            [
                'profileStatusHandler' => $this->profileStatusHandlerMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementInitial($handleProfileStatus)
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(false);
        $paymentMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_INITIAL);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('setIsInitialPaid')
            ->with(true);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $paymentMock],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($paymentMock);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);


        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementTrial($handleProfileStatus)
    {
        $trialCount = 1;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(false);
        $paymentMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_TRIAL);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('getTrialCount')
            ->willReturn($trialCount);
        $scheduleMock->expects($this->once())
            ->method('setTrialCount')
            ->with($trialCount + 1);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $paymentMock],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($paymentMock);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);

        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementRegular($handleProfileStatus)
    {
        $regularCount = 1;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(false);
        $paymentMock->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_REGULAR);
        $paymentMock->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('getRegularCount')
            ->willReturn($regularCount);
        $scheduleMock->expects($this->once())
            ->method('setRegularCount')
            ->with($regularCount + 1);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $paymentMock],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($paymentMock);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);

        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementInitialBundled($handleProfileStatus)
    {
        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $childPayment = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(true);
        $paymentMock->expects($this->once())
            ->method('getChildItems')
            ->willReturn([$childPayment]);
        $childPayment->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_INITIAL);
        $childPayment->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('setIsInitialPaid')
            ->with(true);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $childPayment],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($childPayment);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);

        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementTrialBundled($handleProfileStatus)
    {
        $trialCount = 1;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $childPayment = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(true);
        $paymentMock->expects($this->once())
            ->method('getChildItems')
            ->willReturn([$childPayment]);
        $childPayment->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_TRIAL);
        $childPayment->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('getTrialCount')
            ->willReturn($trialCount);
        $scheduleMock->expects($this->once())
            ->method('setTrialCount')
            ->with($trialCount + 1);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $childPayment],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($childPayment);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);

        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @param bool $handleProfileStatus
     * @dataProvider boolDataProvider
     */
    public function testIncrementRegularBundled($handleProfileStatus)
    {
        $regularCount = 1;

        /** @var PaymentInterface|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(PaymentInterface::class);
        $childPayment = $this->createMock(PaymentInterface::class);
        $scheduleMock = $this->createMock(ScheduleInterface::class);

        $paymentMock->expects($this->once())
            ->method('isBundled')
            ->willReturn(true);
        $paymentMock->expects($this->once())
            ->method('getChildItems')
            ->willReturn([$childPayment]);
        $childPayment->expects($this->once())
            ->method('getPaymentPeriod')
            ->willReturn(PaymentInterface::PERIOD_REGULAR);
        $childPayment->expects($this->once())
            ->method('getSchedule')
            ->willReturn($scheduleMock);
        $scheduleMock->expects($this->once())
            ->method('getRegularCount')
            ->willReturn($regularCount);
        $scheduleMock->expects($this->once())
            ->method('setRegularCount')
            ->with($regularCount + 1);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $childPayment],
                ['schedule' => $scheduleMock]
            );
        if ($handleProfileStatus) {
            $this->profileStatusHandlerMock->expects($this->once())
                ->method('handle')
                ->with($childPayment);
        }
        $paymentMock->expects($this->once())
            ->method('setRetriesCount')
            ->with(0);

        $this->incrementor->increment($paymentMock, $handleProfileStatus);
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [[true], [false]];
    }
}
