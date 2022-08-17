<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class Initial
 *
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
class Initial extends AbstractProfileGroup
{
    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return self::CODE_INITIAL;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemPrice($item, $useBaseCurrency)
    {
        $option = $this->getItemOption($item);
        if ($option) {
            $planDefinition = $option->getPlan()->getDefinition();

            if ($useBaseCurrency) {
                $result = $this->getBasePeriodPrice($item, $planDefinition);
                $result += $planDefinition->getIsInitialFeeEnabled()
                    ? (float)$item->getBaseInitialFee()
                    : 0;
            } else {
                $result = $this->getPeriodPrice($item, $planDefinition);
                $result += $planDefinition->getIsInitialFeeEnabled()
                    ? (float)$item->getInitialFee()
                    : 0;
            }

            if ($this->isBundleChild($item)) {
                $childrenCount = count($item->getParentItem()->getChildItems());
                $result /= $childrenCount;
                $result /= $item->getQty();
            }
        } else {
            $result = $item->getRegularPrice();
        }

        return $result;
    }

    /**
     * Check is bundle child item
     *
     * @param CartItemInterface|ProfileItemInterface $item
     * @return bool
     */
    private function isBundleChild($item)
    {
        return $item->getParentItem() && $item->getParentItem()->getProductType() == BundleType::TYPE_CODE;
    }

    /**
     * @param CartItemInterface|ProfileItemInterface $item
     * @param PlanDefinitionInterface $planDefinition
     * @return float
     */
    private function getBasePeriodPrice($item, $planDefinition)
    {
        $item = $item->getParentItem() ?: $item;

        return $planDefinition->getIsTrialPeriodEnabled()
            ? $item->getBaseTrialPrice()
            : $item->getBaseRegularPrice();
    }

    /**
     * @param CartItemInterface|ProfileItemInterface $item
     * @param PlanDefinitionInterface $planDefinition
     * @return float
     */
    private function getPeriodPrice($item, $planDefinition)
    {
        $item = $item->getParentItem() ?: $item;

        return $planDefinition->getIsTrialPeriodEnabled()
            ? $item->getTrialPrice()
            : $item->getRegularPrice();
    }
}
