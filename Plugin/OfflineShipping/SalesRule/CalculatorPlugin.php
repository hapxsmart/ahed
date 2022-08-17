<?php
namespace Aheadworks\Sarp2\Plugin\OfflineShipping\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Calculator;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class CalculatorPlugin
{
    /**
     * Check if free shipping validation is required
     *
     * @param Calculator $subject
     * @param AbstractItem $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcessFreeShipping($subject, $item)
    {
        if ($item->getProduct()->getForceValidateFreeShipping()) {
            $item->getProduct()->setForceValidate(true);
        }

        return [$item];
    }
}
