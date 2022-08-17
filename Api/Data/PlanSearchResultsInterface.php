<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface PlanSearchResultsInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PlanSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get plans list
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanInterface[]
     */
    public function getItems();

    /**
     * Set plans list
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
