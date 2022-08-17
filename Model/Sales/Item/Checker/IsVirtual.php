<?php
namespace Aheadworks\Sarp2\Model\Sales\Item\Checker;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class IsVirtual
 * @package Aheadworks\Sarp2\Model\Sales\Item\Checker
 */
class IsVirtual
{
    /**
     * Check if items presents a virtual quote/order
     *
     * @param QuoteItem[]|OrderItem[] $items
     * @return bool
     */
    public function check($items)
    {
        foreach ($items as $item) {
            if (!$item->getIsVirtual()) {
                return false;
            }
        }
        return true;
    }
}
