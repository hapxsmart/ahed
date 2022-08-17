<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Action;

use Aheadworks\Sarp2\Engine\Payment\ActionInterface;
use Aheadworks\Sarp2\Engine\Payment\Action\Factory;
use Aheadworks\Sarp2\Engine\Payment\Action\Pool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Action\Pool
 */
class PoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->factoryMock = $this->createMock(Factory::class);
        $this->pool = $objectManager->getObject(
            Pool::class,
            ['factory' => $this->factoryMock]
        );
    }

    public function testGetAction()
    {
        $actionType = ActionInterface::TYPE_SINGLE;
        $actionClassName = 'SingleAction';

        $actionMock = $this->createMock(ActionInterface::class);

        $this->factoryMock->expects($this->once())
            ->method('create')
            ->with($actionClassName)
            ->willReturn($actionMock);

        $class = new \ReflectionClass($this->pool);

        $actionsProperty = $class->getProperty('actions');
        $actionsProperty->setAccessible(true);
        $actionsProperty->setValue($this->pool, [$actionType => $actionClassName]);

        $this->assertSame($actionMock, $this->pool->getAction($actionType));
    }

    public function testGetActionCaching()
    {
        $actionType = ActionInterface::TYPE_SINGLE;
        $actionClassName = 'SingleAction';

        $actionMock = $this->createMock(ActionInterface::class);

        $class = new \ReflectionClass($this->pool);

        $actionsProperty = $class->getProperty('actions');
        $actionsProperty->setAccessible(true);
        $actionsProperty->setValue($this->pool, [$actionType => $actionClassName]);

        $instancesProperty = $class->getProperty('instances');
        $instancesProperty->setAccessible(true);
        $instancesProperty->setValue($this->pool, [$actionType => $actionMock]);

        $this->assertSame($actionMock, $this->pool->getAction($actionType));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown payment action: invalid_action_type requested
     */
    public function testGetActionInvalidActionTypeException()
    {
        $class = new \ReflectionClass($this->pool);

        $actionsProperty = $class->getProperty('actions');
        $actionsProperty->setAccessible(true);
        $actionsProperty->setValue($this->pool, [ActionInterface::TYPE_SINGLE => 'SingleAction']);
        $this->expectException(\InvalidArgumentException::class);
        $this->pool->getAction('invalid_action_type');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ClassName doesn't implement Aheadworks\Sarp2\Engine\Payment\ActionInterface
     */
    public function testGetActionCreateException()
    {
        $actionType = ActionInterface::TYPE_SINGLE;
        $className = 'ClassName';

        $this->factoryMock->expects($this->once())
            ->method('create')
            ->with($className)
            ->willThrowException(
                new \InvalidArgumentException($className . ' doesn\'t implement ' . ActionInterface::class)
            );
        $this->expectException(\InvalidArgumentException::class);
        $class = new \ReflectionClass($this->pool);

        $actionsProperty = $class->getProperty('actions');
        $actionsProperty->setAccessible(true);
        $actionsProperty->setValue($this->pool, [$actionType => $className]);

        $this->pool->getAction($actionType);
    }
}
