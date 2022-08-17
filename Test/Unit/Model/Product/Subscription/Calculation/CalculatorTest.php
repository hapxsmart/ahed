<?php
namespace Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Calculation;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyPool;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PlanPriceCalculator;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Rounder;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PeriodPriceCalculator as PriceCalculation;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CalculatorTest
 *
 * @package Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Calculation
 */
class CalculatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PriceCalculation
     */
    private $calculator;

    /**
     * @var PlanRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planRepositoryMock;

    /**
     * @var Rounder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $roundingMock;

    /**
     * @var StrategyPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $planDataResolverStrategyPool;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->planRepositoryMock = $this->createMock(PlanRepositoryInterface::class);
        $this->roundingMock = $this->createMock(Rounder::class);
        $this->planDataResolverStrategyPool = $this->createMock(StrategyPool::class);

        $this->calculator = $objectManager->getObject(
            PlanPriceCalculator::class,
            [
                'planRepository' => $this->planRepositoryMock,
                'rounding' => $this->roundingMock,
                'planDataResolverStrategyPool' => $this->planDataResolverStrategyPool
            ]
        );
    }

    /**
     * Test calculateAccordingPlan method
     *
     * @param float $price
     * @param float $percent
     * @param int $rounding
     * @param float $expectedResult
     * @dataProvider calculateAndRoundDataProvider
     * @throws NoSuchEntityException
     */
    public function testCalculateAccordingPlan($price, $percent, $rounding, $expectedResult)
    {
        $planId = 10;
        $strategyType = StrategyPool::TYPE_REGULAR;

        $planMock = $this->createMock(PlanInterface::class);
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willReturn($planMock);
        $strategyMock = $this->createMock(StrategyInterface::class);
        $strategyMock->expects($this->once())
            ->method('getPricePatternPercent')
            ->willReturn($percent);
        $this->planDataResolverStrategyPool->expects($this->once())
            ->method('getStrategy')
            ->willReturn($strategyMock);
        $planMock->expects($this->once())
            ->method('getPriceRounding')
            ->willReturn($rounding);
        $this->roundingMock->expects($this->once())
            ->method('round')
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->calculator->calculateAccordingPlan($price, $planId, $strategyType));
    }

    /**
     * Test getAutoTrialPrice method if no plan found
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity!
     */
    public function testGetAutoTrialPriceNoPlan()
    {
        $planId = 10;
        $this->planRepositoryMock->expects($this->once())
            ->method('get')
            ->with($planId)
            ->willThrowException(new NoSuchEntityException(__('No such entity!')));
        $this->expectException(NoSuchEntityException::class);
        $this->calculator->calculateAccordingPlan(10, $planId, 'type');
    }

    /**
     * @return array
     */
    public function calculateAndRoundDataProvider()
    {
        return [
            [10.00, 90.00, PriceRounding::UP_TO_XX_99, 9.99],
            [10.00, 90.00, PriceRounding::UP_TO_XX_90, 9.90],
            [10.00, 90.00, PriceRounding::UP_TO_X9_00, 9],
            [10.00, 90.00, PriceRounding::DOWN_TO_XX_99, 8.99],
            [10.00, 90.00, PriceRounding::DOWN_TO_XX_90, 8.90],
            [10.00, 90.00, PriceRounding::DOWN_TO_X9_00, 9],
            [20.00, 90.00, PriceRounding::DOWN_TO_X9_00, 9],
            [34.00, 90.00, PriceRounding::UP_TO_XX_99, 30.99],
            [34.00, 90.00, PriceRounding::UP_TO_XX_90, 30.90],
            [34.00, 90.00, PriceRounding::UP_TO_X9_00, 29],
            [34.00, 90.00, PriceRounding::DOWN_TO_XX_99, 29.99],
            [34.00, 90.00, PriceRounding::DOWN_TO_XX_90, 29.9],
            [34.00, 90.00, PriceRounding::DOWN_TO_X9_00, 29.00],
            [128.00, 90.00, PriceRounding::UP_TO_X9_00, 119],
            [128.00, 90.00, PriceRounding::UP_TO_XX_99, 115.99],
            [128.00, 90.00, PriceRounding::UP_TO_XX_90, 115.9],
            [128.00, 90.00, PriceRounding::DOWN_TO_X9_00, 109],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_90, 114.90],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_99, 114.99],
            [1234.00, 90.00, PriceRounding::UP_TO_X9_00, 1119],
            [1234.00, 90.00, PriceRounding::DOWN_TO_X9_00, 1109],
            [1234.00, 80.00, PriceRounding::UP_TO_X9_00, 989],
            [1234.00, 80.00, PriceRounding::DOWN_TO_X9_00, 979],
            [1234.00, 81.00, PriceRounding::UP_TO_X9_00, 1009],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_99, 999.99],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_90, 999.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_X9_00, 999],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_90, 998.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_99, 998.99],
        ];
    }
}
