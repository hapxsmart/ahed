<?php
namespace Aheadworks\Sarp2\Test\Unit\Model;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Model\Plan;
use Aheadworks\Sarp2\Model\PlanRepository;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\PlanRepository
 */
class PlanRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @var PlanResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var PlanInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planFactoryMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->resourceMock = $this->createMock(PlanResource::class);
        $this->planFactoryMock = $this->createMock(PlanInterfaceFactory::class);
        $this->planRepository = $objectManager->getObject(
            PlanRepository::class,
            [
                'resource' => $this->resourceMock,
                'planFactory' => $this->planFactoryMock
            ]
        );
    }

    public function testSave()
    {
        $planId = 1;

        /** @var PlanInterface|\PHPUnit_Framework_MockObject_MockObject $planToSaveMock */
        $planToSaveMock = $this->createMock(Plan::class);
        $loadedPlanMock = $this->createMock(Plan::class);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($planToSaveMock);
        $planToSaveMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($loadedPlanMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($loadedPlanMock, $planId);
        $loadedPlanMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $this->assertSame($loadedPlanMock, $this->planRepository->save($planToSaveMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Name is required.
     */
    public function testSaveCouldNotSaveException()
    {
        $exceptionMessage = 'Name is required.';
        /** @var PlanInterface|\PHPUnit_Framework_MockObject_MockObject $planMock */
        $planMock = $this->createMock(Plan::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($planMock)
            ->willThrowException(
                new \Exception($exceptionMessage)
            );
        $this->expectException(CouldNotSaveException::class);
        $this->planRepository->save($planMock);
    }

    public function testGet()
    {
        $planId = 1;

        $planMock = $this->createMock(Plan::class);

        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($planMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($planMock, $planId);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $this->assertSame($planMock, $this->planRepository->get($planId));
        $this->planRepository->get($planId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with planId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $planId = 1;

        $planMock = $this->createMock(Plan::class);

        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($planMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($planMock, $planId);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn(null);
        $this->expectException(NoSuchEntityException::class);
        $this->planRepository->get($planId);
    }

    public function testDeleteById()
    {
        $planId = 1;

        $planMock = $this->createMock(Plan::class);

        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($planMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($planMock, $planId);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($planMock);

        $this->assertTrue($this->planRepository->deleteById($planId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with planId = 1
     */
    public function testDeleteByIdNoSuchEntityException()
    {
        $planId = 1;

        $planMock = $this->createMock(Plan::class);

        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($planMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($planMock, $planId);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn(null);
        $this->expectException(NoSuchEntityException::class);
        $this->planRepository->deleteById($planId);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Could not delete the plan: Mysql error
     */
    public function testDeleteByIdCouldNotDeleteException()
    {
        $planId = 1;
        $exceptionMessage = 'Mysql error';

        $planMock = $this->createMock(Plan::class);

        $this->planFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($planMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($planMock, $planId);
        $planMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($planMock)
            ->willThrowException(new \Exception($exceptionMessage));
        $this->expectException(CouldNotDeleteException::class);
        $this->planRepository->deleteById($planId);
    }
}
