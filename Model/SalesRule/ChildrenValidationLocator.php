<?php
namespace Aheadworks\Sarp2\Model\SalesRule;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator as MagentoChildrenValidationLocator;

class ChildrenValidationLocator extends MagentoChildrenValidationLocator
{
    /**
     * @var array
     */
    private $productTypeChildrenValidationMap;

    /**
     * Checks necessity to validate rule on item's children.
     *
     * @param ProfileItemInterface $item
     * @return bool
     */
    public function isProfileItemChildrenValidationRequired(ProfileItemInterface $item): bool
    {
        $type = $item->getProduct()->getTypeId();
        return isset($this->productTypeChildrenValidationMap[$type])
            ? (bool)$this->productTypeChildrenValidationMap[$type]
            : true;
    }
}
