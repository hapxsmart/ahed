<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StateInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\Iteration\StatesGenerator;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Aheadworks\Sarp2\Engine\Payment\Processor\Pool;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultInterface;
use Magento\Framework\App\Area;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\App\Emulation;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Engine
 */
class EngineTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Engine
     */
    private $engine;

    /**
     * @var PaymentsList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentsListMock;

    /**
     * @var Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $processorPoolMock;

    /**
     * @var StatesGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $iterationStatesGeneratorMock;

    /**
     * @var Cleaner|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cleanerMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var Emulation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appEmulationMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->paymentsListMock = $this->createMock(PaymentsList::class);
        $this->processorPoolMock = $this->createMock(Pool::class);
        $this->iterationStatesGeneratorMock = $this->createMock(StatesGenerator::class);
        $this->cleanerMock = $this->createMock(Cleaner::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->appEmulationMock = $this->createMock(Emulation::class);
        $this->engine = $objectManager->getObject(
            Engine::class,
            [
                'paymentsList' => $this->paymentsListMock,
                'processorPool' => $this->processorPoolMock,
                'iterationStatesGenerator' => $this->iterationStatesGeneratorMock,
                'cleaner' => $this->cleanerMock,
                'logger' => $this->loggerMock,
                'appEmulation' => $this->appEmulationMock
            ]
        );
    }

    /**
     * @param array|null $paymentIds
     * @dataProvider paymentIdsDataProvider
     */
    public function testProcess($paymentIds)
    {
        $storeId = 1;
        $timezoneOffset = 10;
        $paymentType = PaymentInterface::TYPE_PLANNED;

        $iterationStateMock = $this->createMock(StateInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $processorMock = $this->createMock(ProcessorInterface::class);
        $processResultMock = $this->createMock(ResultInterface::class);

        $this->appEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND);
        $this->appEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');

        $this->cleanerMock->expects($this->once())
            ->method('reset');
        $this->iterationStatesGeneratorMock->expects($this->once())
            ->method('generate')
            ->willReturn([$iterationStateMock]);
        $iterationStateMock->expects($this->once())
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $iterationStateMock->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn($storeId);
        $iterationStateMock->expects($this->once())
            ->method('getTimezoneOffset')
            ->willReturn($timezoneOffset);
        $this->paymentsListMock->expects($this->once())
            ->method('getProcessablePaymentsForToday')
            ->with(
                $paymentType,
                $storeId,
                $timezoneOffset,
                $paymentIds
            )
            ->willReturn([$paymentMock]);
        $this->processorPoolMock->expects($this->once())
            ->method('getProcessor')
            ->with($paymentType)
            ->willReturn($processorMock);
        $processorMock->expects($this->once())
            ->method('process')
            ->with([$paymentMock])
            ->willReturn($processResultMock);
        $processResultMock->expects($this->once())
            ->method('isOutstandingDetected')
            ->willReturn(false);
        $this->cleanerMock->expects($this->once())
            ->method('cleanup');

        $class = new \ReflectionClass($this->engine);

        $method = $class->getMethod('process');
        $method->setAccessible(true);

        $method->invokeArgs($this->engine, [$paymentIds]);
    }

    /**
     * @param array|null $paymentIds
     * @dataProvider paymentIdsDataProvider
     */
    public function testProcessWithOutstanding($paymentIds)
    {
        $storeId = 1;
        $timezoneOffset = 10;
        $paymentType = PaymentInterface::TYPE_PLANNED;

        $iterationStateMock = $this->createMock(StateInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $processorMock = $this->createMock(ProcessorInterface::class);
        $processResultMock = $this->createMock(ResultInterface::class);

        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND);
        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('stopEnvironmentEmulation');

        $this->cleanerMock->expects($this->once())
            ->method('reset');
        $this->iterationStatesGeneratorMock->expects($this->exactly(2))
            ->method('generate')
            ->willReturn([$iterationStateMock]);
        $iterationStateMock->expects($this->exactly(2))
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $iterationStateMock->expects($this->exactly(4))
            ->method('getStoreId')
            ->willReturn($storeId);
        $iterationStateMock->expects($this->exactly(2))
            ->method('getTimezoneOffset')
            ->willReturn($timezoneOffset);
        $this->paymentsListMock->expects($this->exactly(2))
            ->method('getProcessablePaymentsForToday')
            ->with(
                $paymentType,
                $storeId,
                $timezoneOffset,
                $paymentIds
            )
            ->willReturn([$paymentMock]);
        $this->processorPoolMock->expects($this->exactly(2))
            ->method('getProcessor')
            ->with($paymentType)
            ->willReturn($processorMock);
        $processorMock->expects($this->exactly(2))
            ->method('process')
            ->with([$paymentMock])
            ->willReturn($processResultMock);
        $processResultMock->expects($this->exactly(2))
            ->method('isOutstandingDetected')
            ->willReturnOnConsecutiveCalls(true, false);
        $this->cleanerMock->expects($this->once())
            ->method('cleanup');

        $class = new \ReflectionClass($this->engine);

        $method = $class->getMethod('process');
        $method->setAccessible(true);

        $method->invokeArgs($this->engine, [$paymentIds]);
    }

    /**
     * @param array|null $paymentIds
     * @dataProvider paymentIdsDataProvider
     */
    public function testProcessException($paymentIds)
    {
        $storeId = 1;
        $timezoneOffset = 10;
        $paymentType = PaymentInterface::TYPE_PLANNED;
        $exception = new \Exception();

        $iterationStateMock = $this->createMock(StateInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $processorMock = $this->createMock(ProcessorInterface::class);

        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND);
        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('stopEnvironmentEmulation');

        $this->cleanerMock->expects($this->once())
            ->method('reset');
        $this->iterationStatesGeneratorMock->expects($this->once())
            ->method('generate')
            ->willReturn([$iterationStateMock]);
        $iterationStateMock->expects($this->once())
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $iterationStateMock->expects($this->exactly(2))
            ->method('getStoreId')
            ->willReturn($storeId);
        $iterationStateMock->expects($this->once())
            ->method('getTimezoneOffset')
            ->willReturn($timezoneOffset);
        $this->paymentsListMock->expects($this->once())
            ->method('getProcessablePaymentsForToday')
            ->with(
                $paymentType,
                $storeId,
                $timezoneOffset,
                $paymentIds
            )
            ->willReturn([$paymentMock]);
        $this->processorPoolMock->expects($this->once())
            ->method('getProcessor')
            ->with($paymentType)
            ->willReturn($processorMock);
        $processorMock->expects($this->once())
            ->method('process')
            ->with([$paymentMock])
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_UNEXPECTED_EXCEPTION,
                ['payments' => [$paymentMock]],
                ['exception' => $exception]
            );
        $this->cleanerMock->expects($this->once())
            ->method('cleanup');

        $class = new \ReflectionClass($this->engine);

        $method = $class->getMethod('process');
        $method->setAccessible(true);

        $method->invokeArgs($this->engine, [$paymentIds]);
    }

    /**
     * @param array|null $paymentIds
     * @dataProvider paymentIdsDataProvider
     */
    public function testProcessCyclesLimitExceeded($paymentIds)
    {
        $maxCycles = Engine::MAX_PROCESS_CYCLES;
        $storeId = 1;
        $timezoneOffset = 10;
        $paymentType = PaymentInterface::TYPE_PLANNED;

        $iterationStateMock = $this->createMock(StateInterface::class);
        $paymentMock = $this->createMock(Payment::class);
        $processorMock = $this->createMock(ProcessorInterface::class);
        $processResultMock = $this->createMock(ResultInterface::class);

        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('startEnvironmentEmulation')
            ->with($storeId, Area::AREA_FRONTEND);
        $this->appEmulationMock->expects($this->atLeastOnce())
            ->method('stopEnvironmentEmulation');

        $this->cleanerMock->expects($this->once())
            ->method('reset');
        $this->iterationStatesGeneratorMock->expects($this->exactly($maxCycles))
            ->method('generate')
            ->willReturn([$iterationStateMock]);
        $iterationStateMock->expects($this->exactly($maxCycles))
            ->method('getPaymentType')
            ->willReturn($paymentType);
        $iterationStateMock->expects($this->exactly($maxCycles + $maxCycles))
            ->method('getStoreId')
            ->willReturn($storeId);
        $iterationStateMock->expects($this->exactly($maxCycles))
            ->method('getTimezoneOffset')
            ->willReturn($timezoneOffset);
        $this->paymentsListMock->expects($this->exactly($maxCycles))
            ->method('getProcessablePaymentsForToday')
            ->with(
                $paymentType,
                $storeId,
                $timezoneOffset,
                $paymentIds
            )
            ->willReturn([$paymentMock]);
        $this->processorPoolMock->expects($this->exactly($maxCycles))
            ->method('getProcessor')
            ->with($paymentType)
            ->willReturn($processorMock);
        $processorMock->expects($this->exactly($maxCycles))
            ->method('process')
            ->with([$paymentMock])
            ->willReturn($processResultMock);
        $processResultMock->expects($this->exactly($maxCycles))
            ->method('isOutstandingDetected')
            ->willReturn(true);
        $this->cleanerMock->expects($this->once())
            ->method('cleanup');

        $class = new \ReflectionClass($this->engine);

        $method = $class->getMethod('process');
        $method->setAccessible(true);

        $method->invokeArgs($this->engine, [$paymentIds]);
    }

    /**
     * @return array
     */
    public function paymentIdsDataProvider()
    {
        return [[null], [[5]]];
    }
}
