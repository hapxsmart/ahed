<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription;

use Aheadworks\Sarp2\Model\Product\Subscription\Option;
use Aheadworks\Sarp2\Model\Product\Subscription\OptionRepository;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option\CollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Test for \Aheadworks\Sarp2\Model\Product\Subscription\OptionRepository
 */
class OptionRepositoryTest extends TestCase
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    /**
     * @var OptionResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var SubscriptionOptionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactoryMock;

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
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->createMock(OptionResource::class);
        $this->optionFactoryMock = $this->createMock(SubscriptionOptionInterfaceFactory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $this->optionRepository = $objectManager->getObject(
            OptionRepository::class,
            [
                'resource' => $this->resourceMock,
                'optionFactory' => $this->optionFactoryMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesJoinProcessorMock,
                'storeManager' => $this->storeManagerMock,
            ]
        );
    }

    /**
     * Test save method
     */
    public function testSave()
    {
        $optionToSaveMock = $this->createMock(Option::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($optionToSaveMock)
            ->willReturnSelf();

        $this->assertSame($optionToSaveMock, $this->optionRepository->save($optionToSaveMock));
    }

    /**
     * Test save method if an error occurs
     *
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Error!
     */
    public function testSaveCouldNotSaveException()
    {
        $optionToSaveMock = $this->createMock(Option::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($optionToSaveMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotSaveException::class);
        $this->optionRepository->save($optionToSaveMock);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $optionId = 1;
        $optionMock = $this->createMock(Option::class);
        $optionMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($optionMock, $optionId)
            ->willReturnSelf();

        $this->assertSame($optionMock, $this->optionRepository->get($optionId));
    }

    /**
     * Test get method if no option found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with optionId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $optionId = 1;
        $optionMock = $this->createMock(Option::class);
        $optionMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn(null);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($optionMock, $optionId)
            ->willReturnSelf();
        $this->expectException(NoSuchEntityException::class);
        $this->optionRepository->get($optionId);
    }

    /**
     * Test getList method
     */
    public function testGetList()
    {
        $storeId = 1;
        $productId = 100;

        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock
            ->method('create')
            ->willReturn($collectionMock);

        $this->extensionAttributesJoinProcessorMock->expects($this->once())
            ->method('process')
            ->with($collectionMock, SubscriptionOptionInterface::class)
            ->willReturnSelf();

        $collectionMock->expects($this->once())
            ->method('addProductFilter')
            ->with($productId)
            ->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addStoreFilter')
            ->with($storeId)
            ->willReturnSelf();

        $optionModelMock = $this->createMock(Option::class);
        $optionModelMock->expects($this->once())
            ->method('getData')
            ->willReturn([
                'option_id' => 200,
            ]);
        $collectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$optionModelMock]));

        $optionMock = $this->getMockForAbstractClass(SubscriptionOptionInterface::class);
        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $this->assertEquals([$optionMock], $this->optionRepository->getList($productId));
    }

    /**
     * Test deleteById method
     */
    public function testDeleteById()
    {
        $optionId = 1;
        $optionMock = $this->createMock(Option::class);
        $optionMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($optionMock, $optionId)
            ->willReturnSelf();
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($optionMock)
            ->willReturnSelf();

        $this->assertTrue($this->optionRepository->deleteById($optionId));
    }

    /**
     * Test deleteById method if no option found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with optionId = 1
     */
    public function testDeleteByIdNoSuchEntityException()
    {
        $optionId = 1;
        $optionMock = $this->createMock(Option::class);
        $optionMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn(null);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($optionMock, $optionId);
        $this->expectException(NoSuchEntityException::class);
        $this->optionRepository->deleteById($optionId);
    }

    /**
     * Test deleteById method if an option can not be deleted
     *
     * @expectedException \Magento\Framework\Exception\CouldNotDeleteException
     * @expectedExceptionMessage Could not delete the subscription option: Error!
     */
    public function testDeleteByIdCouldNotDeleteException()
    {
        $optionId = 1;
        $optionMock = $this->createMock(Option::class);
        $optionMock->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($optionMock, $optionId);
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($optionMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotDeleteException::class);
        $this->optionRepository->deleteById($optionId);
    }
}
