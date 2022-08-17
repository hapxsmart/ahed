<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Group;

use Aheadworks\Sarp2\Model\Sales\Total\Group\AbstractGroup;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Initial extends AbstractGroup
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
        $result = 0.0;
        $optionId = $item->getOptionByCode('aw_sarp2_subscription_type');
        if ($optionId) {
            $option = $this->optionRepository->get($optionId->getValue());
            $planDefinition = $option->getPlan()->getDefinition();

            $initialFee = $option->getInitialFee();
            $baseItemPrice = $initialFee != 0 && $planDefinition->getIsInitialFeeEnabled()
                ? (float)$initialFee
                : 0;
            $result = $useBaseCurrency
                ? $baseItemPrice
                : $this->priceCurrency->convertAndRound($baseItemPrice);

            if ($this->isBundleChild($item)) {
                $childrenCount = count($item->getParentItem()->getChildren());
                $result /= $childrenCount;
                $result /= $item->getQty();
            }
        }

        return $result;
    }

    /**
     * Check is bundle child item
     *
     * @param ItemInterface|AbstractItem $item
     * @return bool
     */
    private function isBundleChild($item)
    {
        return $item->getParentItem() && $item->getParentItem()->getProductType() == BundleType::TYPE_CODE;
    }
}
