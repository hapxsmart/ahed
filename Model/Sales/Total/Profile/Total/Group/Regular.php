<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

/**
 * Class Regular
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Regular extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_REGULAR;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $result = 0.0;
        $option = $this->getItemOption($item);
        if ($option) {
            $calculationInput = $this->createCalculationInput($item);

            $baseItemPrice = $this->priceCalculator->getRegularPrice($calculationInput, $option);
            $result = $useBaseCurrency
                ? $baseItemPrice
                : $this->priceCurrency->convert($baseItemPrice);

            $result = $this->customOptionCalculator->applyOptionsPrice($item, $result, $useBaseCurrency, false);
        } else {
            $result = $item->getRegularPrice();
        }

        return $result;
    }
}
