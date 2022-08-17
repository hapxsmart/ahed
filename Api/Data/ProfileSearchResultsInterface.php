<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProfileSearchResultsInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get profiles list
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface[]
     */
    public function getItems();

    /**
     * Set profiles list
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
