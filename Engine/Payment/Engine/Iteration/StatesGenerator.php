<?php
namespace Aheadworks\Sarp2\Engine\Payment\Engine\Iteration;

use Aheadworks\Sarp2\Engine\Payment\Processor\Pool;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDate;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StatesGenerator
 * @package Aheadworks\Sarp2\Engine\Payment\Engine\Iteration
 */
class StatesGenerator
{
    /**
     * @var Pool
     */
    private $processorPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CoreDate
     */
    private $coreDate;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * @var StateInterface[]
     */
    private $states;

    /**
     * @param Pool $processorPool
     * @param StoreManagerInterface $storeManager
     * @param CoreDate $coreDate
     * @param TimezoneInterface $localeDate
     * @param StateFactory $stateFactory
     */
    public function __construct(
        Pool $processorPool,
        StoreManagerInterface $storeManager,
        CoreDate $coreDate,
        TimezoneInterface $localeDate,
        StateFactory $stateFactory
    ) {
        $this->processorPool = $processorPool;
        $this->storeManager = $storeManager;
        $this->coreDate = $coreDate;
        $this->localeDate = $localeDate;
        $this->stateFactory = $stateFactory;
    }

    /**
     * Generate iteration states
     *
     * @return StateInterface[]
     */
    public function generate()
    {
        if (!$this->states) {
            $states = [];
            $paymentTypes = $this->processorPool->getConfiguredPaymentTypes();
            foreach ($this->getAllStoreIds() as $storeId) {

                /**
                 * @param string $paymentType
                 * @return void
                 */
                $addStatesCallback = function ($paymentType) use (&$states, $storeId) {
                    $states[] = $this->stateFactory->create(
                        [
                            'storeId' => $storeId,
                            'paymentType' => $paymentType,
                            'tmzOffset' => $this->getTimezoneOffset($storeId)
                        ]
                    );
                };
                array_walk($paymentTypes, $addStatesCallback);
            }
            $this->states = $states;
        }
        return $this->states;
    }

    /**
     * Get all store Ids
     *
     * @return array
     */
    private function getAllStoreIds()
    {
        /**
         * @param StoreInterface $store
         * @return int
         */
        $closure = function ($store) {
            return $store->getId();
        };
        return array_map($closure, $this->storeManager->getStores());
    }

    /**
     * Get timezone offset of specified store Id
     *
     * @param int $storeId
     * @return int
     */
    private function getTimezoneOffset($storeId)
    {
        $timezone = $this->localeDate->getConfigTimezone(
            $storeId,
            ScopeInterface::SCOPE_STORE
        );
        return $this->coreDate->calculateOffset($timezone);
    }
}
