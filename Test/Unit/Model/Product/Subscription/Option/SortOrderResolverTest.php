<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\SortOrderResolver;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\Option\SortOrderResolver
 */
class SortOrderResolverTest extends TestCase
{
    /**
     * @var SortOrderResolver
     */
    private $model;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->planRepositoryMock = $this->createMock(PlanRepositoryInterface::class);

        $this->model = $objectManager->getObject(
            SortOrderResolver::class,
            [
                'planRepository' => $this->planRepositoryMock,
            ]
        );
    }

    /**
     * Test getSortOrder method
     *
     * @param $sortOrder
     * @dataProvider getSortOrderDataProvider
     */
    public function testGetSortOrder($sortOrder)
    {
        $planId = 11;

        $optionMock = $this->createMock(SubscriptionOptionInterface::class);
        $optionMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $planMock = $this->createMock(PlanInterface::class);
        $planMock->expects($this->once())
            ->method('getSortOrder')
            ->willReturn($sortOrder);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);

        $this->assertEquals($sortOrder, $this->model->getSortOrder($optionMock));
    }

    /**
     * @return array
     */
    public function getSortOrderDataProvider()
    {
        return [
            ['sortOrder' => null],
            ['sortOrder' => 5],
            ['sortOrder' => 10],
        ];
    }

    /**
     * Test getSortOrder method if an exception occurs
     */
    public function testGetSortOrderException()
    {
        $planId = 11;

        $optionMock = $this->createMock(SubscriptionOptionInterface::class);
        $optionMock->expects($this->once())
            ->method('getPlanId')
            ->willReturn($planId);

        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));

        $this->assertNull($this->model->getSortOrder($optionMock));
    }
}
