<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyPool;
use Aheadworks\Sarp2\Model\Plan\Source\PriceRounding;

/**
 * Class PlanPriceCalculator
 */
class PlanPriceCalculator
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var Rounder
     */
    private $rounding;

    /**
     * @var StrategyPool
     */
    private $planDataResolverStrategyPool;

    /**
     * @param PlanRepositoryInterface $planRepository
     * @param Rounder $rounding
     * @param StrategyPool $planDataResolverStrategyPool
     */
    public function __construct(
        PlanRepositoryInterface $planRepository,
        Rounder $rounding,
        StrategyPool $planDataResolverStrategyPool
    ) {
        $this->planRepository = $planRepository;
        $this->rounding = $rounding;
        $this->planDataResolverStrategyPool = $planDataResolverStrategyPool;
    }

    /**
     * Calculate and round amount according with subscription plan
     *
     * @param float $amount
     * @param int|PlanInterface $plan
     * @param string $strategyType
     * @param int|null $forceRoundingType
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculateAccordingPlan($amount, $plan, $strategyType, $forceRoundingType = null)
    {
        $plan = $this->getPlan($plan);
        $strategy = $this->planDataResolverStrategyPool->getStrategy($strategyType);
        $percent = $strategy->getPricePatternPercent($plan);
        $roundingType = (0 != $percent)
            ? $plan->getPriceRounding()
            : PriceRounding::DONT_ROUND;
        if (null != $forceRoundingType) {
            $roundingType = $forceRoundingType;
        }

        return $this->calculate($amount, $percent, $roundingType);
    }

    /**
     * Calculate amount with percent and round by plan rounding type
     *
     * @param float $amount
     * @param float $percent
     * @param int $roundingType
     * @return float
     */
    private function calculate($amount, $percent, $roundingType)
    {
        return $this->rounding->round(
            $amount * $percent / 100,
            $roundingType
        );
    }

    /**
     * Retrieve plan
     *
     * @param PlanInterface|int $plan
     * @return PlanInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPlan($plan)
    {
        if ($plan instanceof PlanInterface) {
            return $plan;
        }

        return $this->planRepository->get($plan);
    }
}
