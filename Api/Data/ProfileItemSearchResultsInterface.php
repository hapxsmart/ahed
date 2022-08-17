<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ProfileItemSearchResultsInterface
 *
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get profile item list
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemInterface[]
     */
    public function getItems();

    /**
     * Set profile item list
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
