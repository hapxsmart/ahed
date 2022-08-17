<?php
namespace Aheadworks\Sarp2\Model\Quote\Item\Grouping;

use Magento\Quote\Model\Quote\Item;

/**
 * Interface CriterionInterface
 * @package Aheadworks\Sarp2\Model\Quote\Item\Grouping
 */
interface CriterionInterface
{
    /**
     * Get grouping criterion value
     *
     * @param Item $quoteItem
     * @return string|null
     */
    public function getValue($quoteItem);

    /**
     * Get result item name
     *
     * @return string
     */
    public function getResultName();

    /**
     * Get grouping result value
     *
     * @param Item $quoteItem
     * @return mixed
     */
    public function getResultValue($quoteItem);
}
