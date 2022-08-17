<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationFactory;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\CustomOptionCalculator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Directory\Model\Currency;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

/**
 * Test for \Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group\Trial
 */
class TrialTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Calculation trial price
     */
    const CALCULATION_PRICE = 12.00;

    /**
     * @var Trial
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
            Trial::class,
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
     * @param float $trialPrice
     * @param bool $isTrialPeriodEnabled
     * @param bool $isAutoTrialPrice
     * @param bool $useBaseCurrency
     * @param float $expectedResult
     * @dataProvider getItemPriceDataProvider
     */
    public function testGetItemPrice(
        $trialPrice,
        $isTrialPeriodEnabled,
        $isAutoTrialPrice,
        $useBaseCurrency,
        $expectedResult
    ) {
        $subscriptionOptionId = 1;
        $qty = 1;

        /** @var ItemInterface|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(Item::class);
        $optionMock = $this->createMock(OptionInterface::class);
        $subscriptionOptionMock = $this->createMock(SubscriptionOptionInterface::class);
        $planMock = $this->createMock(PlanInterface::class);
        $definitionMock = $this->createMock(PlanDefinitionInterface::class);
        $productMock = $this->createMock(Product::class);
        $forcedCurrency = $this->createMock(Currency::class);
        $calculationInputMock = $this->createMock(Input::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getForcedCurrency'])
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);
        $itemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('aw_sarp2_subscription_type')
            ->willReturn($optionMock);
        $itemMock->expects($this->any())
            ->method('getQty')
            ->willReturn(1);
        $optionMock->expects($this->once())
            ->method('getValue')
            ->willReturn($subscriptionOptionId);
        $this->optionRepositoryMock->expects($this->once())
            ->method('get')
            ->with($subscriptionOptionId)
            ->willReturn($subscriptionOptionMock);
        $subscriptionOptionMock->expects($this->once())
            ->method('getPlan')
            ->willReturn($planMock);
        $planMock->expects($this->once())
            ->method('getDefinition')
            ->willReturn($definitionMock);
        $definitionMock->expects($this->once())
            ->method('getIsTrialPeriodEnabled')
            ->willReturn($isTrialPeriodEnabled);
        if ($isTrialPeriodEnabled) {
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
                ->method('getTrialPrice')
                ->willReturn($expectedResult);

            if (!$useBaseCurrency) {
                $quoteMock->expects($this->any())
                    ->method('getForcedCurrency')
                    ->willReturn($forcedCurrency);
                $forcedCurrency->expects($this->once())
                    ->method('getCode')
                    ->willReturn('USD');
                $this->priceCurrencyMock->expects($this->once())
                    ->method('convertAndRound')
                    ->willReturnArgument(0);
            }
        }

        $this->customOptionCalculatorMock->expects($this->once())
            ->method('applyOptionsPrice')
            ->with($itemMock, $expectedResult, $useBaseCurrency)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->totalGroup->getItemPrice($itemMock, $useBaseCurrency));
    }

    /**
     * @return array
     */
    public function getItemPriceDataProvider()
    {
        return [
            [8.00, true, false, true, 8.00],
            [8.00, true, false, false, 8.00],
            [8.00, true, true, true, self::CALCULATION_PRICE],
            [8.00, true, true, false, self::CALCULATION_PRICE],
            [8.00, false, false, true, 0.00],
            [8.00, false, true, true, 0.00]
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
            ->method('__call')->with('getHasChildren')
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

    /**
     * @return array
     */
    public function getItemPriceQuoteItemDataProvider()
    {
        return [
            [8.00, true, false, true, true, 8.00],
            [8.00, true, false, false, true, 8.00],
            [8.00, true, true, true, true, self::CALCULATION_PRICE],
            [8.00, true, true, false, true, self::CALCULATION_PRICE],
            [8.00, false, false, true, true, 0.00],
            [8.00, false, true, true, true, 0.00],
            [8.00, true, false, true, false, 8.00],
            [8.00, true, false, false, false, 8.00],
            [8.00, true, true, true, false, self::CALCULATION_PRICE],
            [8.00, true, true, false, false, self::CALCULATION_PRICE],
            [8.00, false, false, true, false, 0.00],
            [8.00, false, true, true, false, 0.00]
        ];
    }
}
