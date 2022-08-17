<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface PaymentTokenSearchResultsInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PaymentTokenSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get payment tokens list
     *
     * @return \Aheadworks\Sarp2\Api\Data\PaymentTokenInterface[]
     */
    public function getItems();

    /**
     * Set payment tokens list
     *
     * @param \Aheadworks\Sarp2\Api\Data\PaymentTokenInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
