<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Quote\Item\Checker;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription as ProductChecker;
use Magento\Catalog\Model\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;

/**
 * Test for \Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription
 */
class IsSubscriptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IsSubscription
     */
    private $checker;

    /**
     * @var ProductChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productCheckerMock;

    /**
     * @var Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private $itemMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->productCheckerMock = $this->createMock(ProductChecker::class);
        $this->itemMock = $this->createMock(Item::class);
        $this->checker = $objectManager->getObject(
            IsSubscription::class,
            ['productChecker' => $this->productCheckerMock]
        );
    }

    public function testCheckSubscription()
    {
        $productMock = $this->createMock(Product::class);
        $optionMock = $this->createMock(Option::class);
        $optionValue = 1;

        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->productCheckerMock->expects($this->once())
            ->method('check')
            ->with($productMock)
            ->willReturn(true);
        $this->itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($optionValue);

        $this->assertTrue($this->checker->check($this->itemMock));
    }

    public function testCheckNonSubscriptionProduct()
    {
        $productMock = $this->createMock(Product::class);

        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->productCheckerMock->expects($this->once())
            ->method('check')
            ->with($productMock)
            ->willReturn(false);

        $this->assertFalse($this->checker->check($this->itemMock));
    }

    public function testCheckNoSubscriptionTypeOption()
    {
        $productMock = $this->createMock(Product::class);

        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->productCheckerMock->expects($this->once())
            ->method('check')
            ->with($productMock)
            ->willReturn(true);
        $this->itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn(null);

        $this->assertFalse($this->checker->check($this->itemMock));
    }

    public function testCheckOneOffPurchaseOption()
    {
        $productMock = $this->createMock(Product::class);
        $optionMock = $this->createMock(Option::class);

        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->productCheckerMock->expects($this->once())
            ->method('check')
            ->with($productMock)
            ->willReturn(true);
        $this->itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn(0);

        $this->assertFalse($this->checker->check($this->itemMock));
    }
}
