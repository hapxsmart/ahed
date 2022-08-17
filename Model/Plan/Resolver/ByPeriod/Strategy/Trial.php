<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\Strategy;

use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyInterface;

/**
 * Class Trial
 *
 * @package Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod
 */
class Trial implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getPricePatternPercent($plan)
    {
        return $plan->getTrialPricePatternPercent();
    }
}
