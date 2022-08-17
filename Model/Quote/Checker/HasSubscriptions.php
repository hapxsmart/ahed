<?php
namespace Aheadworks\Sarp2\Model\Quote\Checker;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription as ItemChecker;
use Magento\Quote\Model\Quote;

/**
 * Class HasSubscriptions
 * @package Aheadworks\Sarp2\Model\Quote\Checker
 */
class HasSubscriptions
{
    /**
     * @var ItemChecker
     */
    private $itemChecker;

    /**
     * @var array
     */
    private $checkResult = [];

    /**
     * @param ItemChecker $itemChecker
     */
    public function __construct(ItemChecker $itemChecker)
    {
        $this->itemChecker = $itemChecker;
    }

    /**
     * Check if quote has subscription items
     *
     * @param Quote $quote
     * @return bool
     */
    public function check($quote)
    {
        $checkResult = $this->performCheck($quote);
        return $checkResult['has_subscription_items'];
    }

    /**
     * Check if quote has subscription items only
     *
     * @param Quote $quote
     * @return bool
     */
    public function checkHasSubscriptionsOnly($quote)
    {
        $checkResult = $this->performCheck($quote);
        return $checkResult['has_subscription_items']
            && !$checkResult['has_one_off_items'];
    }

    /**
     * Check if quote has both subscription items and one-off items
     *
     * @param Quote $quote
     * @return bool
     */
    public function checkHasBoth($quote)
    {
        $checkResult = $this->performCheck($quote);
        return $checkResult['has_subscription_items']
            && $checkResult['has_one_off_items'];
    }

    /**
     * Perform quote check
     *
     * @param Quote $quote
     * @return array
     */
    private function performCheck($quote)
    {
        $quoteId = $quote->getId();
        if (!isset($this->checkResult[$quoteId])) {
            $this->checkResult[$quoteId] = [
                'has_subscription_items' => false,
                'has_one_off_items' => false
            ];

            $items = $quote->getAllItems();
            foreach ($items as $item) {
                if ($this->itemChecker->check($item)) {
                    $this->checkResult[$quoteId]['has_subscription_items'] = true;
                } else {
                    $this->checkResult[$quoteId]['has_one_off_items'] = true;
                }
            }
        }
        return $this->checkResult[$quoteId];
    }
}
