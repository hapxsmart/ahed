<?php
namespace Aheadworks\Sarp2\Test\Unit\Engine\Payment\Schedule;

use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Schedule as ScheduleResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence
 */
class PersistenceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var ScheduleResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ScheduleFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scheduleFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(ScheduleResource::class);
        $this->scheduleFactoryMock = $this->createMock(ScheduleFactory::class);
        $this->persistence = $objectManager->getObject(
            Persistence::class,
            [
                'resource' => $this->resourceMock,
                'scheduleFactory' => $this->scheduleFactoryMock
            ]
        );
    }

    public function testGet()
    {
        $scheduleId = 1;

        $scheduleMock = $this->createMock(Schedule::class);

        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($scheduleMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($scheduleMock, $scheduleId);
        $scheduleMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn($scheduleId);

        $this->assertSame($scheduleMock, $this->persistence->get($scheduleId));
    }

    public function testGetCaching()
    {
        $scheduleId = 1;

        $scheduleMock = $this->createMock(Schedule::class);

        $class = new \ReflectionClass($this->persistence);

        $instancesByIdProperty = $class->getProperty('instancesById');
        $instancesByIdProperty->setAccessible(true);
        $instancesByIdProperty->setValue($this->persistence, [$scheduleId => $scheduleMock]);

        $this->resourceMock->expects($this->never())
            ->method('load');

        $this->assertSame($scheduleMock, $this->persistence->get($scheduleId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with scheduleId = 1
     */
    public function testGetException()
    {
        $scheduleId = 1;

        $scheduleMock = $this->createMock(Schedule::class);

        $this->scheduleFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($scheduleMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($scheduleMock, $scheduleId);
        $scheduleMock->expects($this->once())
            ->method('getScheduleId')
            ->willReturn(null);
        $this->expectException(NoSuchEntityException::class);
        $this->persistence->get($scheduleId);
    }
}
