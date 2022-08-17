<?php
namespace Aheadworks\Sarp2\Test\Unit\Model;

use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ProfileRepository;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Test for \Aheadworks\Sarp2\Model\ProfileRepository
 */
class ProfileRepositoryTest extends TestCase
{
    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var ProfileResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ProfileInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFactoryMock;

    /**
     * @var CollectorList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $totalCollectorListMock;

    /**
     * @var ProfileSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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

        $this->resourceMock = $this->createMock(ProfileResource::class);
        $this->profileFactoryMock = $this->createMock(ProfileInterfaceFactory::class);
        $this->totalCollectorListMock = $this->createMock(CollectorList::class);
        $this->searchResultsFactoryMock = $this->createMock(ProfileSearchResultsInterfaceFactory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);

        $this->profileRepository = $objectManager->getObject(
            ProfileRepository::class,
            [
                'resource' => $this->resourceMock,
                'profileFactory' => $this->profileFactoryMock,
                'totalCollectorList' => $this->totalCollectorListMock,
                'searchResultsFactory' => $this->searchResultsFactoryMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
            ]
        );
    }

    /**
     * Test save method
     *
     * @param bool $recolectTotals
     * @dataProvider saveDataProvider
     */
    public function testSave($recolectTotals)
    {
        $profileId = 1;
        $profileToSaveMock = $this->createMock(Profile::class);
        $profileToSaveMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);
        $loadedProfileMock = $this->createMock(Profile::class);
        $loadedProfileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);

        if ($recolectTotals) {
            $collectorMock = $this->createMock(CollectorInterface::class);
            $collectorMock->expects($this->once())
                ->method('collect')
                ->with($profileToSaveMock)
                ->willReturnSelf();
            $this->totalCollectorListMock->expects($this->once())
                ->method('getCollectors')
                ->willReturn([$collectorMock]);
        }
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($profileToSaveMock)
            ->willReturnSelf();
        $this->profileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($loadedProfileMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($loadedProfileMock, $profileId);

        $this->assertSame($loadedProfileMock, $this->profileRepository->save($profileToSaveMock, $recolectTotals));
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            ['recolectTotals' => true],
            ['recolectTotals' => false],
        ];
    }

    /**
     * Test save method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveCouldNotSaveException()
    {
        $profileMock = $this->createMock(Profile::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($profileMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotSaveException::class);
        $this->profileRepository->save($profileMock, false);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $profileId = 1;
        $profileMock = $this->createMock(Profile::class);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn($profileId);

        $this->profileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profileMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($profileMock, $profileId);

        $this->assertSame($profileMock, $this->profileRepository->get($profileId));
    }

    /**
     * Test get method if no profile found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with profileId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $profileId = 1;
        $profileMock = $this->createMock(Profile::class);
        $profileMock->expects($this->once())
            ->method('getProfileId')
            ->willReturn(null);

        $this->profileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profileMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($profileMock, $profileId);
        $this->expectException(NoSuchEntityException::class);
        $this->profileRepository->get($profileId);
    }

    /**
     * Test getList method
     */
    public function testGetList()
    {
        $filterName = 'profile_id';
        $filterValue = 100;
        $collectionSize = 5;
        $scCurrPage = 1;
        $scPageSize = 3;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(ProfileSearchResultsInterface::class);
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
            ->with($collectionMock, ProfileInterface::class)
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
            ->with($filterName, ['eq' => $filterValue]);
        $collectionMock
            ->expects($this->once())
            ->method('getSize')
            ->willReturn($collectionSize);
        $searchResultsMock->expects($this->once())
            ->method('setTotalCount')
            ->with($collectionSize);

        $searchCriteriaMock->expects($this->once())
            ->method('getCurrentPage')
            ->willReturn($scCurrPage);
        $collectionMock->expects($this->once())
            ->method('setCurPage')
            ->with($scCurrPage)
            ->willReturnSelf();
        $searchCriteriaMock->expects($this->once())
            ->method('getPageSize')
            ->willReturn($scPageSize);
        $collectionMock->expects($this->once())
            ->method('setPageSize')
            ->with($scPageSize)
            ->willReturn($collectionMock);

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

        $profileModelMock = $this->createMock(Profile::class);
        $profileModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'profile_id' => 1,
            ]);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$profileModelMock]));

        $profileMock = $this->getMockForAbstractClass(ProfileInterface::class);
        $this->profileFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($profileMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$profileMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->profileRepository->getList($searchCriteriaMock));
    }
}
