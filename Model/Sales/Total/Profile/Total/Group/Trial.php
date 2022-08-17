<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Aheadworks\Sarp2\Model\Plan\Resolver\ByPeriod\StrategyPool;

/**
 * Class Trial
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Trial extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_TRIAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $option = $this->getItemOption($item);
        if ($option) {
            $plan = $option->getPlan();

            if ($plan->getDefinition()->getIsTrialPeriodEnabled()) {
                $calculationInput = $this->createCalculationInput($item);

                $baseItemPrice = $this->priceCalculator->getTrialPrice($calculationInput, $option);
                $result = $useBaseCurrency
                    ? $baseItemPrice
                    : $this->priceCurrency->convert($baseItemPrice);

                $result = $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency, true);
            }
        } else {
            $result = $item->getRegularPrice();
        }

        return $result;
    }
}
