<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Profile\Action;

use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Profile\Action\ApplierPool
 */
class ApplierPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ApplierPool
     */
    private $pool;

    /**
     * @var ApplierFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $applierFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->applierFactoryMock = $this->createMock(ApplierFactory::class);
        $this->pool = $objectManager->getObject(
            ApplierPool::class,
            ['applierFactory' => $this->applierFactoryMock]
        );
    }

    public function testGetApplier()
    {
        $actionType = ActionInterface::ACTION_TYPE_CHANGE_STATUS;
        $applierClassName = 'ChangeStatusApplier';

        $applierMock = $this->createMock(ApplierInterface::class);

        $this->applierFactoryMock->expects($this->once())
            ->method('create')
            ->with($applierClassName)
            ->willReturn($applierMock);

        $class = new \ReflectionClass($this->pool);

        $appliersProperty = $class->getProperty('appliers');
        $appliersProperty->setAccessible(true);
        $appliersProperty->setValue($this->pool, [$actionType => $applierClassName]);

        $this->assertSame($applierMock, $this->pool->getApplier($actionType));
    }

    public function testGetApplierCaching()
    {
        $actionType = ActionInterface::ACTION_TYPE_CHANGE_STATUS;
        $applierClassName = 'ChangeStatusApplier';

        $applierMock = $this->createMock(ApplierInterface::class);

        $class = new \ReflectionClass($this->pool);

        $appliersProperty = $class->getProperty('appliers');
        $appliersProperty->setAccessible(true);
        $appliersProperty->setValue($this->pool, [$actionType => $applierClassName]);

        $applierInstancesProperty = $class->getProperty('applierInstances');
        $applierInstancesProperty->setAccessible(true);
        $applierInstancesProperty->setValue($this->pool, [$actionType => $applierMock]);

        $this->applierFactoryMock->expects($this->never())
            ->method('create')
            ->with($applierClassName);

        $this->assertSame($applierMock, $this->pool->getApplier($actionType));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown action applier: invalid_action_type requested
     */
    public function testGetApplierInvalidActionTypeException()
    {
        $class = new \ReflectionClass($this->pool);

        $appliersProperty = $class->getProperty('appliers');
        $appliersProperty->setAccessible(true);
        $appliersProperty->setValue(
            $this->pool,
            [ActionInterface::ACTION_TYPE_CHANGE_STATUS => 'ChangeStatusApplier']
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->pool->getApplier('invalid_action_type');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ClassName doesn't implement Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface
     */
    public function testGetApplierCreateException()
    {
        $actionType = ActionInterface::ACTION_TYPE_CHANGE_STATUS;
        $className = 'ClassName';

        $this->applierFactoryMock->expects($this->once())
            ->method('create')
            ->with($className)
            ->willThrowException(
                new \InvalidArgumentException($className . ' doesn\'t implement ' . ApplierInterface::class)
            );
        $this->expectException(\InvalidArgumentException::class);
        $class = new \ReflectionClass($this->pool);

        $appliersProperty = $class->getProperty('appliers');
        $appliersProperty->setAccessible(true);
        $appliersProperty->setValue($this->pool, [$actionType => $className]);

        $this->pool->getApplier($actionType);
    }
}
