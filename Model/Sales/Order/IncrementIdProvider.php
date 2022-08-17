<?php
namespace Aheadworks\Sarp2\Model\Sales\Order;

use Magento\Sales\Model\Order;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class IncrementIdProvider
 * @package Aheadworks\Sarp2\Model\Sales\Order
 */
class IncrementIdProvider
{
    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param SequenceManager $sequenceManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SequenceManager $sequenceManager,
        StoreManagerInterface $storeManager
    ) {
        $this->sequenceManager = $sequenceManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Get reserved order increment Id
     *
     * @param int $storeId
     * @return string
     */
    public function getIncrementId($storeId)
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);
        return $this->sequenceManager
            ->getSequence(Order::ENTITY, $store->getId())
            ->getNextValue();
    }
}
