<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\Strategy;

use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyInterface;

/**
 * Class Regular
 *
 * @package Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod
 */
class Regular implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getPricePatternPercent($plan)
    {
        return $plan->getRegularPricePatternPercent();
    }
}
