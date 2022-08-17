<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Profile;

use Aheadworks\Sarp2\Model\Profile\Order;
use Aheadworks\Sarp2\Model\Profile\OrderRepository;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\ProfileRepository
 */
class OrderRepositoryTest extends TestCase
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ProfileOrderInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileOrderFactoryMock;

    /**
     * @var ProfileOrderSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var JoinProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->profileOrderFactoryMock = $this->createMock(ProfileOrderInterfaceFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(ProfileOrderSearchResultsInterfaceFactory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);

        $this->orderRepository = $objectManager->getObject(
            OrderRepository::class,
            [
                'profileOrderFactory' => $this->profileOrderFactoryMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $filterName = 'order_id';
        $filterValue = 100;
        $collectionSize = 5;
        $currentPage = 1;
        $currentPageSize = 10;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(ProfileOrderSearchResultsInterface::class);
        $searchResultsMock->expects($this->atLeastOnce())
            ->method('setSearchCriteria')
            ->with($searchCriteriaMock)
            ->willReturnSelf();
        $this->searchResultsFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($searchResultsMock);

        $collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, ProfileOrderInterface::class)
            ->willReturnSelf();

        $filterGroupMock = $this->createMock(FilterGroup::class);
        $filterMock = $this->createMock(Filter::class);

        $searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);
        $filterGroupMock->expects($this->once())
            ->method('getFilters')
            ->willReturn([$filterMock]);
        $filterMock->expects($this->once())
            ->method('getConditionType')
            ->willReturn(false);
        $filterMock->expects($this->atLeastOnce())
            ->method('getField')
            ->willReturn($filterName);
        $filterMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($filterValue);
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with([$filterName], [['eq' => $filterValue]]);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $sortOrderMock = $this->createMock(SortOrder::class);
        $searchCriteriaMock->expects($this->atLeastOnce())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);
        $sortOrderMock->expects($this->once())
            ->method('getField')
            ->willReturn($filterName);
        $collectionMock->expects($this->once())
            ->method('addOrder')
            ->with($filterName, SortOrder::SORT_ASC);
        $sortOrderMock->expects($this->once())
            ->method('getDirection')
            ->willReturn(SortOrder::SORT_ASC);

        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($currentPage);
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($currentPageSize);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($currentPage)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($currentPageSize)
            ->willReturnSelf();

        $profileOrderModelMock = $this->createMock(Order::class);
        $profileOrderModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'profile_id' => 1,
            ]);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$profileOrderModelMock]));

        $profileOrderMock = $this->getMockForAbstractClass(ProfileOrderInterface::class);
        $this->profileOrderFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profileOrderMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$profileOrderMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->orderRepository->getList($searchCriteriaMock));
    }
}
