<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod;

use Aheadworks\Sarp2\Api\Data\PlanInterface;

/**
 * Interface StrategyInterface
 *
 * @package Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod
 */
interface StrategyInterface
{
    /**
     * Get price percentage of product price
     *
     * @param PlanInterface $plan
     * @return float
     */
    public function getPricePatternPercent($plan);
}
