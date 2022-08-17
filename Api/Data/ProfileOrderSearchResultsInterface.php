<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProfileOrderSearchResultsInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileOrderSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get profile orders list
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileOrderInterface[]
     */
    public function getItems();

    /**
     * Set profile orders list
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileOrderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
