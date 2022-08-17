<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Payment;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\Model\Payment\TokenRepository;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token\CollectionFactory;
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
 * Test for \Aheadworks\Sarp2\Model\Profile\AddressRepository
 */
class TokenRepositoryTest extends TestCase
{
    /**
     * @var TokenRepository
     */
    private $tokenRepository;

    /**
     * @var TokenResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var PaymentTokenInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenFactoryMock;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
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

        $this->resourceMock = $this->createMock(TokenResource::class);
        $this->tokenFactoryMock = $this->createMock(PaymentTokenInterfaceFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(PaymentTokenSearchResultsInterfaceFactory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);

        $this->tokenRepository = $objectManager->getObject(
            TokenRepository::class,
            [
                'resource' => $this->resourceMock,
                'tokenFactory' => $this->tokenFactoryMock,
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
        $tokenId = 1;
        $tokenToSaveMock = $this->createMock(Token::class);
        $tokenToSaveMock->expects($this->once())
            ->method('getTokenId')
            ->willReturn($tokenId);
        $loadedTokenMock = $this->createMock(Token::class);
        $loadedTokenMock->expects($this->once())
            ->method('getTokenId')
            ->willReturn($tokenId);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($tokenToSaveMock)
            ->willReturnSelf();
        $this->tokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($loadedTokenMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($loadedTokenMock, $tokenId);

        $this->assertSame($loadedTokenMock, $this->tokenRepository->save($tokenToSaveMock));
    }

    /**
     * Test save method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveCouldNotSaveException()
    {
        $tokenToSaveMock = $this->createMock(Token::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($tokenToSaveMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotSaveException::class);
        $this->tokenRepository->save($tokenToSaveMock);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $tokenId = 1;
        $tokenMock = $this->createMock(Token::class);
        $tokenMock->expects($this->once())
            ->method('getTokenId')
            ->willReturn($tokenId);

        $this->tokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tokenMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($tokenMock, $tokenId);

        $this->assertSame($tokenMock, $this->tokenRepository->get($tokenId));
    }

    /**
     * Test get method if no token found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with tokenId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $tokenId = 1;
        $tokenMock = $this->createMock(Token::class);
        $tokenMock->expects($this->once())
            ->method('getTokenId')
            ->willReturn(null);

        $this->tokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tokenMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($tokenMock, $tokenId);
        $this->expectException(NoSuchEntityException::class);
        $this->tokenRepository->get($tokenId);
    }

    /**
     * Test getList method
     */
    public function testGetList()
    {
        $filterName = 'token_id';
        $filterValue = 100;
        $collectionSize = 5;

        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $searchResultsMock = $this->createMock(PaymentTokenSearchResultsInterface::class);
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
            ->with($collectionMock, PaymentTokenInterface::class)
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

        $tokenModelMock = $this->createMock(Token::class);
        $tokenModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'token_id' => 1,
            ]);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$tokenModelMock]));

        $tokenMock = $this->getMockForAbstractClass(PaymentTokenInterface::class);
        $this->tokenFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tokenMock);
        $searchResultsMock->expects($this->once())
            ->method('setItems')
            ->with([$tokenMock])
            ->willReturnSelf();

        $this->assertSame($searchResultsMock, $this->tokenRepository->getList($searchCriteriaMock));
    }
}
