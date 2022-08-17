<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment as PaymentResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Persistence
 */
class PersistenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var PaymentResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(PaymentResource::class);
        $this->paymentFactoryMock = $this->createMock(PaymentFactory::class);
        $this->persistence = $objectManager->getObject(
            Persistence::class,
            [
                'resource' => $this->resourceMock,
                'paymentFactory' => $this->paymentFactoryMock
            ]
        );
    }

    public function testSave()
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock);
        $this->assertSame($paymentMock, $this->persistence->save($paymentMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to save payment entity.
     */
    public function testSaveException()
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($paymentMock)
            ->willThrowException(new \Exception('Unable to save payment entity.'));
        $this->expectException(CouldNotSaveException::class);
        $this->persistence->save($paymentMock);
    }

    public function testMassSave()
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('massUpdate')
            ->with([$paymentMock]);
        $this->persistence->massSave([$paymentMock]);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to update payment entities.
     */
    public function testMassSaveException()
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('massUpdate')
            ->with([$paymentMock])
            ->willThrowException(new \Exception('Unable to update payment entities.'));
        $this->expectException(CouldNotSaveException::class);
        $this->persistence->massSave([$paymentMock]);
    }

    /**
     * @param bool $withSchedule
     * @dataProvider getMassDeleteDataProvider
     */
    public function testMassDelete($withSchedule)
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('massDelete')
            ->with([$paymentMock], $withSchedule);
        $this->persistence->massDelete([$paymentMock], $withSchedule);
    }

    /**
     * @param bool $withSchedule
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Unable to delete payment entities.
     * @dataProvider getMassDeleteDataProvider
     */
    public function testMassDeleteException($withSchedule)
    {
        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $this->resourceMock->expects($this->once())
            ->method('massDelete')
            ->with([$paymentMock], $withSchedule)
            ->willThrowException(new \Exception('Unable to delete payment entities.'));
        $this->expectException(CouldNotDeleteException::class);
        $this->persistence->massDelete([$paymentMock], $withSchedule);
    }

    public function testMassChangeStatus()
    {
        $paymentId = 1;
        $status = PaymentInterface::STATUS_UNPROCESSABLE;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeStatus')
            ->with([$paymentId], $status);
        $this->persistence->massChangeStatus([$paymentMock], $status);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to update payment entities.
     */
    public function testMassChangeStatusException()
    {
        $paymentId = 1;
        $status = PaymentInterface::STATUS_UNPROCESSABLE;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeStatus')
            ->with([$paymentId], $status)
            ->willThrowException(new \Exception('Unable to update payment entities.'));
        $this->expectException(CouldNotSaveException::class);
        $this->persistence->massChangeStatus([$paymentMock], $status);
    }

    public function testMassChangeType()
    {
        $paymentId = 1;
        $type = PaymentInterface::TYPE_OUTSTANDING;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeType')
            ->with([$paymentId], $type);
        $this->persistence->massChangeType([$paymentMock], $type);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to update payment entities.
     */
    public function testMassChangeTypeException()
    {
        $paymentId = 1;
        $type = PaymentInterface::TYPE_OUTSTANDING;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeType')
            ->with([$paymentId], $type)
            ->willThrowException(new \Exception('Unable to update payment entities.'));
        $this->expectException(CouldNotSaveException::class);
        $this->persistence->massChangeType([$paymentMock], $type);
    }

    public function testMassChangeStatusAndType()
    {
        $paymentId = 1;
        $status = PaymentInterface::STATUS_RETRYING;
        $type = PaymentInterface::TYPE_OUTSTANDING;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeStatusAndType')
            ->with([$paymentId], $status, $type);
        $this->persistence->massChangeStatusAndType([$paymentMock], $status, $type);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Unable to update payment entities.
     */
    public function testMassChangeStatusAndTypeException()
    {
        $paymentId = 1;
        $status = PaymentInterface::STATUS_RETRYING;
        $type = PaymentInterface::TYPE_OUTSTANDING;

        /** @var Payment|\PHPUnit_Framework_MockObject_MockObject $paymentMock */
        $paymentMock = $this->createMock(Payment::class);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);
        $this->resourceMock->expects($this->once())
            ->method('changeStatusAndType')
            ->with([$paymentId], $status, $type)
            ->willThrowException(new \Exception('Unable to update payment entities.'));
        $this->expectException(CouldNotSaveException::class);
        $this->persistence->massChangeStatusAndType([$paymentMock], $status, $type);
    }

    public function testGet()
    {
        $paymentId = 1;

        $paymentMock = $this->createMock(Payment::class);

        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($paymentMock, $paymentId);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn($paymentId);

        $this->assertSame($paymentMock, $this->persistence->get($paymentId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 1
     */
    public function testGetException()
    {
        $paymentId = 1;

        $paymentMock = $this->createMock(Payment::class);

        $this->paymentFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($paymentMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($paymentMock, $paymentId);
        $paymentMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->expectException(NoSuchEntityException::class);
        $this->persistence->get($paymentId);
    }

    /**
     * @return array
     */
    public function getMassDeleteDataProvider()
    {
        return [[false, true]];
    }
}
