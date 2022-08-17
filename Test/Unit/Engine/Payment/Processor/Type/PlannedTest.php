<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\Config;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\Detector;
use Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding\DetectResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\Result;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\Candidate;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\BundlesGrouper;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle\GroupResult;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Copy;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned
 */
class PlannedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Planned
     */
    private $processor;

    /**
     * @var BundlesGrouper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundlesGrouperMock;

    /**
     * @var Detector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outstandingDetectorMock;

    /**
     * @var Copy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $copyServiceMock;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $engineConfigMock;

    /**
     * @var PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFactoryMock;

    /**
     * @var IsProcessable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $isProcessableCheckerMock;

    /**
     * @var Cleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanerMock;

    /**
     * @var ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->bundlesGrouperMock = $this->createMock(BundlesGrouper::class);
        $this->outstandingDetectorMock = $this->createMock(Detector::class);
        $this->copyServiceMock = $this->createMock(Copy::class);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->engineConfigMock = $this->createMock(Config::class);
        $this->paymentFactoryMock = $this->createMock(PaymentFactory::class);
        $this->isProcessableCheckerMock = $this->createMock(IsProcessable::class);
        $this->cleanerMock = $this->createMock(Cleaner::class);
        $this->resultFactoryMock = $this->createMock(ResultFactory::class);
        $this->processor = $objectManager->getObject(
            Planned::class,
            [
                'bundlesGrouper' => $this->bundlesGrouperMock,
                'outstandingDetector' => $this->outstandingDetectorMock,
                'copyService' => $this->copyServiceMock,
                'persistence' => $this->persistenceMock,
                'engineConfig' => $this->engineConfigMock,
                'paymentFactory' => $this->paymentFactoryMock,
                'isProcessableChecker' => $this->isProcessableCheckerMock,
                'cleaner' => $this->cleanerMock,
                'resultFactory' => $this->resultFactoryMock
            ]
        );
    }

    public function testProcessSingle()
    {
        $paymentsMock = [$this->createMock(Payment::class)];
        $outstandingDetectResultMock = $this->createMock(DetectResult::class);
        $groupResultMock = $this->createMock(GroupResult::class);
        $actualPaymentMock = $this->createMock(Payment::class);
        $resultMock = $this->createMock(Result::class);
        $paymentsMock = $this->splitPaymentsByStatus($paymentsMock);

        $paymentsMock = $paymentsMock[PaymentInterface::STATUS_PLANNED];
        foreach ($paymentsMock as $paymentMock) {
            $this->isProcessableCheckerMock->expects($this->once())
                ->method('check')
                ->with($paymentMock, PaymentInterface::TYPE_PLANNED)
                ->willReturn(true);
        }
        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with($paymentsMock)
            ->willReturn($outstandingDetectResultMock);
        $outstandingDetectResultMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectResultMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn($paymentsMock);
        $this->engineConfigMock->expects($this->once())
            ->method('isBundledPaymentsEnabled')
            ->willReturn(true);
        $this->bundlesGrouperMock->expects($this->once())
            ->method('group')
            ->with($paymentsMock)
            ->willReturn($groupResultMock);
        $groupResultMock->expects($this->once())
            ->method('getSinglePayments')
            ->willReturn($paymentsMock);
        $groupResultMock->expects($this->once())
            ->method('getBundleCandidates')
            ->willReturn([]);
        $this->persistenceMock->expects($this->once())
            ->method('massChangeStatus')
            ->with($paymentsMock, PaymentInterface::STATUS_UNPROCESSABLE);
        foreach ($paymentsMock as $paymentMock) {
            $this->paymentFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($actualPaymentMock);
            $this->copyServiceMock->expects($this->once())
                ->method('copyToSingle')
                ->with($paymentMock, $actualPaymentMock);
            $actualPaymentMock->expects($this->once())
                ->method('setType')
                ->with(PaymentInterface::TYPE_ACTUAL)
                ->willReturnSelf();
            $actualPaymentMock->expects($this->once())
                ->method('setPaymentStatus')
                ->with(PaymentInterface::STATUS_PENDING)
                ->willReturnSelf();
            $this->persistenceMock->expects($this->once())
                ->method('save')
                ->with($actualPaymentMock);
            $this->cleanerMock->expects($this->once())
                ->method('add')
                ->with($paymentMock);
        }
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->processor->process($paymentsMock));
    }

    public function testProcessBundled()
    {
        $parentId = 1;

        $paymentsMock = [$this->createMock(Payment::class)];
        $outstandingDetectResultMock = $this->createMock(DetectResult::class);
        $groupResultMock = $this->createMock(GroupResult::class);
        $bundleCandidateMock = $this->createMock(Candidate::class);
        $parentMock = $this->createMock(Payment::class);
        $resultMock = $this->createMock(Result::class);
        $paymentsMock = $this->splitPaymentsByStatus($paymentsMock);

        $paymentsMock = $paymentsMock[PaymentInterface::STATUS_PLANNED];
        foreach ($paymentsMock as $paymentMock) {
            $this->isProcessableCheckerMock->expects($this->once())
                ->method('check')
                ->with($paymentMock, PaymentInterface::TYPE_PLANNED)
                ->willReturn(true);
        }

        $this->outstandingDetectorMock->expects($this->once())
            ->method('detect')
            ->with($paymentsMock)
            ->willReturn($outstandingDetectResultMock);
        $outstandingDetectResultMock->expects($this->once())
            ->method('getOutstandingPayments')
            ->willReturn([]);
        $outstandingDetectResultMock->expects($this->once())
            ->method('getTodayPayments')
            ->willReturn($paymentsMock);
        $this->engineConfigMock->expects($this->once())
            ->method('isBundledPaymentsEnabled')
            ->willReturn(true);
        $this->bundlesGrouperMock->expects($this->once())
            ->method('group')
            ->with($paymentsMock)
            ->willReturn($groupResultMock);
        $groupResultMock->expects($this->once())
            ->method('getSinglePayments')
            ->willReturn([]);
        $groupResultMock->expects($this->once())
            ->method('getBundleCandidates')
            ->willReturn([$bundleCandidateMock]);
        $bundleCandidateMock->expects($this->once())
            ->method('getParent')
            ->willReturn($parentMock);
        $parentMock->expects($this->once())
            ->method('setType')
            ->with(PaymentInterface::TYPE_ACTUAL)
            ->willReturnSelf();
        $parentMock->expects($this->once())
            ->method('setPaymentStatus')
            ->with(PaymentInterface::STATUS_PENDING)
            ->willReturnSelf();
        $bundleCandidateMock->expects($this->once())
            ->method('getChildren')
            ->willReturn($paymentsMock);
        foreach ($paymentsMock as $paymentMock) {
            $this->isProcessableCheckerMock->expects($this->once())
                ->method('check')
                ->with($paymentMock, PaymentInterface::TYPE_PLANNED)
                ->willReturn(true);
            $paymentMock->expects($this->once())
                ->method('setPaymentStatus')
                ->with(PaymentInterface::STATUS_UNPROCESSABLE)
                ->willReturnSelf();
            $paymentMock->expects($this->once())
                ->method('setParentItem')
                ->with($parentMock)
                ->willReturnSelf();
            $this->persistenceMock->expects($this->once())
                ->method('save')
                ->with($paymentMock);
            $paymentMock->expects($this->once())
                ->method('getParentId')
                ->willReturn($parentId);
        }

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(['isOutstandingDetected' => false])
            ->willReturn($resultMock);

        $this->assertSame($resultMock, $this->processor->process([$paymentMock]));
    }

    public function splitPaymentsByStatus($paymentsMock)
    {
        $paymentStatus = PaymentInterface::STATUS_PLANNED;
        $resultMock = [
            PaymentInterface::STATUS_PLANNED => [],
            PaymentInterface::STATUS_CANCELLED => []
        ];

        foreach ($paymentsMock as $paymentMock) {
            $paymentMock->expects($this->once())
                ->method('getPaymentStatus')
                ->willReturn($paymentStatus);
            $resultMock[$paymentStatus][] = $paymentMock;
        }
        return $resultMock;
    }
}
