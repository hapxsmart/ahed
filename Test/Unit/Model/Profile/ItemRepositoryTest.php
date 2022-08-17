<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Profile;

use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\ItemRepository;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item as ItemResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Aheadworks\Sarp2\Model\Profile\ItemRepository
 */
class ItemRepositoryTest extends TestCase
{
    /**
     * @var ItemRepository
     */
    private $itemRepository;

    /**
     * @var ItemResource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceMock;

    /**
     * @var ProfileItemInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->resourceMock = $this->createMock(ItemResource::class);
        $this->itemFactoryMock = $this->createMock(ProfileItemInterfaceFactory::class);

        $this->itemRepository = $objectManager->getObject(
            ItemRepository::class,
            [
                'resource' => $this->resourceMock,
                'itemFactory' => $this->itemFactoryMock,
            ]
        );
    }

    /**
     * Test save method
     *
     * @param bool $isChild
     * @dataProvider saveDataProvider
     */
    public function testSave($isChild)
    {
        $parentItemId = 1;
        $itemId = 2;
        $itemToSaveMock = $this->createMock(Item::class);
        $itemToSaveMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);
        if ($isChild) {
            $parentItemMock = $this->createMock(Item::class);
            $parentItemMock->expects($this->once())
                ->method('getItemId')
                ->willReturn($parentItemId);
            $itemToSaveMock->expects($this->atLeastOnce())
                ->method('getParentItem')
                ->willReturn($parentItemMock);
        } else {
            $itemToSaveMock->expects($this->atLeastOnce())
                ->method('getParentItem')
                ->willReturn(null);
        }
        $loadedItemMock = $this->createMock(Item::class);
        $loadedItemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($itemToSaveMock)
            ->willReturnSelf();
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($loadedItemMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($loadedItemMock, $itemId);

        $this->assertSame($loadedItemMock, $this->itemRepository->save($itemToSaveMock));
    }

    /**
     * @return array
     */
    public function saveDataProvider()
    {
        return [
            ['isChild' => false],
            ['isChild' => true],
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
        $itemToSaveMock = $this->createMock(Item::class);
        $itemToSaveMock->expects($this->atLeastOnce())
            ->method('getParentItem')
            ->willReturn(null);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($itemToSaveMock)
            ->willThrowException(
                new \Exception('Error!')
            );
        $this->expectException(CouldNotSaveException::class);
        $this->itemRepository->save($itemToSaveMock);
    }

    /**
     * Test get method
     */
    public function testGet()
    {
        $itemId = 1;
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn($itemId);

        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($itemMock, $itemId);

        $this->assertSame($itemMock, $this->itemRepository->get($itemId));
    }

    /**
     * Test get method if no item found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with itemId = 1
     */
    public function testGetNoSuchEntityException()
    {
        $itemId = 1;
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())
            ->method('getItemId')
            ->willReturn(null);

        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);
        $this->resourceMock->expects($this->once())
            ->method('load')
            ->with($itemMock, $itemId);
        $this->expectException(NoSuchEntityException::class);
        $this->itemRepository->get($itemId);
    }
}
