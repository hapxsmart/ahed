<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Checker;

use Magento\Quote\Model\Quote\Item as QuoteItem;

class IsInitial
{
    /**
     * @var IsSubscription
     */
    private $isSubscription;

    /**
     * @param IsSubscription $isSubscription
     */
    public function __construct(IsSubscription $isSubscription)
    {
        $this->isSubscription = $isSubscription;
    }

    /**
     * Check if quote item is for initial subscription
     *
     * @param QuoteItem $quoteItem
     * @return bool
     */
    public function check($quoteItem)
    {
        if ($this->isSubscription->check($quoteItem)) {
            $product = $quoteItem->getParentItem()
                ? $quoteItem->getParentItem()->getProduct()
                : $quoteItem->getProduct();
            return (bool)$product->getCustomOption('aw_sarp2_subscription_type');
        }

        return false;
    }
}
