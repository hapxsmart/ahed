<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationFactory;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Regular;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Regular
 */
class RegularTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Calculation regular price
     */
    const CALCULATION_PRICE = 15.00;

    /**
     * @var Regular
     */
    private $totalGroup;

    /**
     * @var SubscriptionOptionRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $optionRepositoryMock;

    /**
     * @var SubscriptionPriceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCalculationMock;

    /**
     * @var PriceCurrency|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var PopulatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $populatorFactoryMock;

    /**
     * @var ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $providerMock;

    /**
     * @var CustomOptionCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customOptionCalculatorMock;

    /**
     * @var CalculationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $calculationInputFactoryMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->optionRepositoryMock = $this->createMock(SubscriptionOptionRepositoryInterface::class);
        $this->priceCalculationMock = $this->createMock(SubscriptionPriceCalculatorInterface::class);
        $this->priceCurrencyMock = $this->createMock(PriceCurrency::class);
        $this->populatorFactoryMock = $this->createMock(PopulatorFactory::class);
        $this->providerMock = $this->createMock(ProviderInterface::class);
        $this->customOptionCalculatorMock = $this->createMock(CustomOptionCalculator::class);
        $this->calculationInputFactoryMock = $this->createMock(CalculationFactory::class);

        $this->totalGroup = $objectManager->getObject(
            Regular::class,
            [
                'optionRepository' => $this->optionRepositoryMock,
                'priceCalculation' => $this->priceCalculationMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'populatorFactory' => $this->populatorFactoryMock,
                'provider' => $this->providerMock,
                'customOptionCalculator' => $this->customOptionCalculatorMock,
                'calculationInputFactory' => $this->calculationInputFactoryMock
            ]
        );
    }

    /**
     * @param bool $useBaseCurrency
     * @param float $expectedResult
     * @throws \ReflectionException
     * @dataProvider getItemPriceDataProvider
     */
    public function testGetItemPrice(
        $useBaseCurrency,
        $expectedResult
    ) {
        $subscriptionOptionId = 1;
        $qty = 1;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(Item::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $productMock = $this->createMock(Product::class);
        $calculationInputMock = $this->createMock(Input::class);

        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);
        $this->optionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willReturn($subscriptionOptionMock);
        $itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($qty);
        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $itemMock->expects($this->once())
            ->method('getParentItem')
            ->willReturn(false);
        $itemMock->expects($this->any())
            ->method('isChildrenCalculated')
            ->willReturn(false);
        $this->calculationInputFactoryMock->expects($this->once())
            ->method('create')
            ->with($productMock, $qty)
            ->willReturn($calculationInputMock);
        $this->priceCalculationMock->expects($this->once())
            ->method('getRegularPrice')
            ->willReturn(self::CALCULATION_PRICE);
        $this->customOptionCalculatorMock->expects($this->once())
            ->method('applyOptionsPrice')
            ->with($itemMock, self::CALCULATION_PRICE, $useBaseCurrency)
            ->willReturn(self::CALCULATION_PRICE);

        if (!$useBaseCurrency) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndRound')
                ->willReturnArgument(0);
        }

        $this->assertEquals($expectedResult, $this->totalGroup->getItemPrice($itemMock, $useBaseCurrency));
    }

    public function testGetItemPriceNonSubscription()
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ItemInterface::class);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn(null);
        $this->assertEquals(0, $this->totalGroup->getItemPrice($itemMock, true));
    }

    /**
     * @return array
     */
    public function getItemPriceDataProvider()
    {
        return [
            [true, self::CALCULATION_PRICE],
            [false, self::CALCULATION_PRICE],
            [false, self::CALCULATION_PRICE],
            [false, self::CALCULATION_PRICE]
        ];
    }

    /**
     * Get quote item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param bool $hasChildren
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getQuoteItemMock($optionMock, $hasChildren, $productMock)
    {
        if ($hasChildren) {
            $itemMock = $this->getConfigurableItemMock($optionMock, $productMock);
        } else {
            $itemMock = $this->getSimpleItemMock($optionMock, $productMock);
        }

        return $itemMock;
    }

    /**
     * Get configurable item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $childProductMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getConfigurableItemMock($optionMock, $childProductMock)
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(Item::class);
        $childItem = $this->createMock(Item::class);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $itemMock->expects($this->any())
            ->method('__call')
            ->with('getHasChildren')
            ->willReturn(true);
        $itemMock->expects($this->any())
            ->method('getChildren')
            ->willReturn([$childItem]);
        $childItem->expects($this->any())
            ->method('getProduct')
            ->willReturn($childProductMock);

        return $itemMock;
    }

    /**
     * Get simple item mock
     *
     * @param OptionInterface|\PHPUnit_Framework_MockObject_MockObject $optionMock
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $productMock
     * @return Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSimpleItemMock($optionMock, $productMock)
    {
        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(Item::class);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $itemMock->expects($this->any())
            ->method('__call')
            ->with('getHasChildren')
            ->willReturn(false);
        $itemMock->expects($this->any())
            ->method('getProduct')
            ->willReturn($productMock);

        return $itemMock;
    }
}
