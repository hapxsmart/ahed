<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Processor;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Processor\Cleaner
 */
class CleanerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @var Persistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $persistenceMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->persistenceMock = $this->createMock(Persistence::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->cleaner = $objectManager->getObject(
            Cleaner::class,
            [
                'persistence' => $this->persistenceMock,
                'logger' => $this->loggerMock
            ]
        );
    }

    public function testAdd()
    {
        $paymentId = 1;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);

        $paymentMock->expects($this->once())
            ->method('__call')
            ->with('getItemId')
            ->willReturn($paymentId);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PAYMENT_ADDED_TO_CLEANER,
                ['payment' => $paymentMock]
            );

        $class = new \ReflectionClass($this->cleaner);

        $paymentsToDeleteProperty = $class->getProperty('paymentsToDelete');
        $paymentsToDeleteProperty->setAccessible(true);
        $paymentsToDeleteProperty->setValue($this->cleaner, []);

        $this->cleaner->add($paymentMock);
        $this->assertEquals(
            [$paymentId => $paymentMock],
            $paymentsToDeleteProperty->getValue($this->cleaner)
        );
    }

    public function testAddList()
    {
        $paymentId = 1;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);

        $paymentMock->expects($this->once())
            ->method('__call')
            ->with('getItemId')
            ->willReturn($paymentId);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_PAYMENT_ADDED_TO_CLEANER,
                ['payment' => $paymentMock]
            );

        $class = new \ReflectionClass($this->cleaner);

        $paymentsToDeleteProperty = $class->getProperty('paymentsToDelete');
        $paymentsToDeleteProperty->setAccessible(true);
        $paymentsToDeleteProperty->setValue($this->cleaner, []);

        $this->cleaner->addList([$paymentMock]);
        $this->assertEquals(
            [$paymentId => $paymentMock],
            $paymentsToDeleteProperty->getValue($this->cleaner)
        );
    }

    public function testReset()
    {
        $class = new \ReflectionClass($this->cleaner);

        $paymentsToDeleteProperty = $class->getProperty('paymentsToDelete');
        $paymentsToDeleteProperty->setAccessible(true);
        $paymentsToDeleteProperty->setValue($this->cleaner, [1 => $this->createMock(Payment::class)]);

        $this->cleaner->reset();
        $this->assertEquals(
            [],
            $paymentsToDeleteProperty->getValue($this->cleaner)
        );
    }

    public function testRemove()
    {
        $paymentId = 1;

        $class = new \ReflectionClass($this->cleaner);

        $paymentsToDeleteProperty = $class->getProperty('paymentsToDelete');
        $paymentsToDeleteProperty->setAccessible(true);
        $paymentsToDeleteProperty->setValue($this->cleaner, [$paymentId => $this->createMock(Payment::class)]);

        $this->cleaner->remove($paymentId);
        $this->assertEquals(
            [],
            $paymentsToDeleteProperty->getValue($this->cleaner)
        );
    }

    public function testCleanup()
    {
        $paymentId = 1;

        $paymentMock = $this->createMock(Payment::class);

        $class = new \ReflectionClass($this->cleaner);

        $paymentsToDeleteProperty = $class->getProperty('paymentsToDelete');
        $paymentsToDeleteProperty->setAccessible(true);
        $paymentsToDeleteProperty->setValue($this->cleaner, [$paymentId => $paymentMock]);

        $this->persistenceMock->expects($this->once())
            ->method('massDelete')
            ->with([$paymentId => $paymentMock], false);
        $this->loggerMock->expects($this->once())
            ->method('traceProcessing')
            ->with(
                LoggerInterface::ENTRY_CLEANUP,
                ['payments' => [$paymentId => $paymentMock]]
            );

        $this->cleaner->cleanup();
        $this->assertEquals(
            [],
            $paymentsToDeleteProperty->getValue($this->cleaner)
        );
    }
}
