<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Interface CheckerInterface
 *
 * @package Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription
 */
interface CheckerInterface
{
    /**
     * Check if item is subscription
     *
     * @param ItemInterface $item
     * @return bool
     */
    public function check($item);
}
