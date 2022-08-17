<?php
namespace Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface ProviderInterface
 *
 * @package Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions
 */
interface ProviderInterface
{
    /**
     * Get detailed subscription options
     *
     * @param ItemInterface $item
     * @return array
     * @throws LocalizedException
     */
    public function getSubscriptionOptions(ItemInterface $item);
}
