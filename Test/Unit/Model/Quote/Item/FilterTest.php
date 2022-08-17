<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Quote\Item;

use Aheadworks\Sarp2\Model\Quote\Item\Filter;
use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\Model\Quote\Item\Filter
 */
class FilterTest extends TestCase
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var IsSubscription|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemCheckerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->itemCheckerMock = $this->createMock(IsSubscription::class);

        $this->filter = $objectManager->getObject(
            Filter::class,
            [
                'itemChecker' =>  $this->itemCheckerMock,
            ]
        );
    }

    /**
     * Test filterOneOff method
     */
    public function testFilterOneOff()
    {
        $configurableProduct = $this->getItemMock(1, null);
        $configurableChildProduct = $this->getItemMock(2, 1);
        $simpleProduct = $this->getItemMock(3, null);
        $configurableProductSubscription = $this->getItemMock(4, null);
        $configurableChildProductSubscription = $this->getItemMock(5, 4);
        $simpleProductSubscription = $this->getItemMock(6, null);

        $items = [
            $configurableProduct,
            $configurableChildProduct,
            $simpleProduct,
            $configurableProductSubscription,
            $configurableChildProductSubscription,
            $simpleProductSubscription
        ];
        $resultItems = [
            $configurableProductSubscription,
            $configurableChildProductSubscription,
            $simpleProductSubscription
        ];

        $this->itemCheckerMock->expects($this->any())
            ->method('check')
            ->withConsecutive(
                [$configurableProduct],
                [$simpleProduct],
                [$configurableProductSubscription],
                [$simpleProductSubscription]
            )
            ->willReturnOnConsecutiveCalls(false, false, true, true);

        $this->assertEquals($resultItems, $this->filter->filterOneOff($items));
    }

    /**
     * Test filterOneOff method if empty array specified
     */
    public function testFilterOneOffEmpty()
    {
        $this->assertEquals([], $this->filter->filterOneOff([]));
    }

    /**
     * Test filterSubscription method
     */
    public function testFilterSubscription()
    {
        $configurableProduct = $this->getItemMock(1, null);
        $configurableChildProduct = $this->getItemMock(2, 1);
        $simpleProduct = $this->getItemMock(3, null);
        $configurableProductSubscription = $this->getItemMock(4, null);
        $configurableChildProductSubscription = $this->getItemMock(5, 4);
        $simpleProductSubscription = $this->getItemMock(6, null);

        $items = [
            $configurableProduct,
            $configurableChildProduct,
            $simpleProduct,
            $configurableProductSubscription,
            $configurableChildProductSubscription,
            $simpleProductSubscription
        ];
        $resultItems = [
            $configurableProduct,
            $configurableChildProduct,
            $simpleProduct,
        ];

        $this->itemCheckerMock->expects($this->any())
            ->method('check')
            ->withConsecutive(
                [$configurableProduct],
                [$simpleProduct],
                [$configurableProductSubscription],
                [$simpleProductSubscription]
            )
            ->willReturnOnConsecutiveCalls(true, true, false, false);

        $this->assertEquals($resultItems, $this->filter->filterSubscription($items));
    }

    /**
     * Test filterSubscription method if empty array specified
     */
    public function testFilterSubscriptionEmpty()
    {
        $this->assertEquals([], $this->filter->filterSubscription([]));
    }

    /**
     * Get quote item mock
     *
     * @param int $itemId
     * @param int $parentItemId
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getItemMock($itemId, $parentItemId)
    {
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->any())
            ->method('getItemId')
            ->willReturn($itemId);
        $itemMock->expects($this->any())
            ->method('__call')
            ->with('getParentItemId')
            ->willReturn($parentItemId);

        return $itemMock;
    }
}
