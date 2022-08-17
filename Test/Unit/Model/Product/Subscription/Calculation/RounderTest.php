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
 * Class RounderTest
 *
 * @package Aheadworks\Sarp2\Test\Unit\Model\Product\Subscription\Calculation
 */
class RounderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Rounder
     */
    private $rounder;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->rounder = $objectManager->getObject(Rounder::class);
    }

    /**
     * @param float $price
     * @param int $rounding
     * @param float $expectedResult
     * @dataProvider calculateAndRoundDataProvider
     */
    public function testRound($price, $percent, $rounding, $expectedResult)
    {
        $price = $price * $percent / 100;
        $this->assertEquals($expectedResult, $this->rounder->round($price, $rounding));
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
            [128.00, 90.00, PriceRounding::UP_TO_XX_99, 115.99],
            [128.00, 90.00, PriceRounding::UP_TO_XX_90, 115.9],
            [128.00, 90.00, PriceRounding::DOWN_TO_X9_00, 109],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_90, 114.90],
            [128.00, 90.00, PriceRounding::DOWN_TO_XX_99, 114.99],
            [1234.00, 90.00, PriceRounding::DOWN_TO_X9_00, 1109],
            [1234.00, 80.00, PriceRounding::DOWN_TO_X9_00, 979],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_99, 999.99],
            [1234.00, 81.00, PriceRounding::UP_TO_XX_90, 999.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_X9_00, 999],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_90, 998.90],
            [1234.00, 81.00, PriceRounding::DOWN_TO_XX_99, 998.99],
        ];
    }
}
