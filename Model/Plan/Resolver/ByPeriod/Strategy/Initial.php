<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\Strategy;

use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyInterface;

/**
 * Class Initial
 *
 * @package Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\Strategy
 */
class Initial implements StrategyInterface
{
    /**
     * @inheritDoc
     */
    public function getPricePatternPercent($plan)
    {
        $definition = $plan->getDefinition();
        if ($definition->getIsTrialPeriodEnabled()) {
            return $plan->getTrialPricePatternPercent();
        } else {
            return $plan->getRegularPricePatternPercent();
        }
    }
}
