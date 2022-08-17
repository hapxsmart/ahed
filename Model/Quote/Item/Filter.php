<?php
namespace Aheadworks\Sarp2\Model\Quote\Item;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Filter
 * @package Aheadworks\Sarp2\Model\Quote\Item
 */
class Filter
{
    /**
     * @var IsSubscription
     */
    private $itemChecker;

    /**
     * @param IsSubscription $itemChecker
     */
    public function __construct(IsSubscription $itemChecker)
    {
        $this->itemChecker = $itemChecker;
    }

    /**
     * Filter one-off quote items
     *
     * @param Item[] $quoteItems
     * @return Item[]
     */
    public function filterOneOff($quoteItems)
    {
        $resultItems = [];
        foreach ($quoteItems as $index => $quoteItem) {
            if (!$quoteItem->getParentItemId() && $this->itemChecker->check($quoteItem)) {
                $resultItems[] = $quoteItem;
                $childItems = $this->getChildItems($quoteItem->getItemId(), $quoteItems);
                $resultItems = array_merge($resultItems, $childItems);
            }
        }

        return $resultItems;
    }

    /**
     * Filter subscription quote items
     *
     * @param Item[] $quoteItems
     * @return Item[]
     */
    public function filterSubscription($quoteItems)
    {
        $resultItems = [];
        foreach ($quoteItems as $index => $quoteItem) {
            if (!$quoteItem->getParentItemId() && !$this->itemChecker->check($quoteItem)) {
                $resultItems[] = $quoteItem;
                $childItems = $this->getChildItems($quoteItem->getItemId(), $quoteItems);
                $resultItems = array_merge($resultItems, $childItems);
            }
        }

        return $resultItems;
    }

    /**
     * Get child items
     *
     * @param int $parentItemId
     * @param Item[] $quoteItems
     * @return Item[]
     */
    private function getChildItems($parentItemId, $quoteItems)
    {
        $childItems = [];
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getParentItemId() == $parentItemId) {
                $childItems[] = $quoteItem;
            }
        }

        return $childItems;
    }
}
