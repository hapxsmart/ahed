<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface AccessTokenSearchResultsInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface AccessTokenSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get payment tokens list
     *
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenInterface[]
     */
    public function getItems();

    /**
     * Set payment tokens list
     *
     * @param \Aheadworks\Sarp2\Api\Data\AccessTokenInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
