<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PostDataProcessor
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class PostDataProcessor
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PlanRepositoryInterface $planRepository
    ) {
        $this->storeManager = $storeManager;
        $this->planRepository = $planRepository;
    }

    /**
     * Prepare option entity data for save
     *
     * @param array $data
     * @param string $productType
     * @return array
     */
    public function prepareEntityData($data, $productType)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            $stores = $this->storeManager->getStores();
            /** @var StoreInterface $store */
            $store = current($stores);
            $data['website_id'] = $store->getWebsiteId();
        }

        $plan = $this->getPlan($data['plan_id'] ?? 0);

        if ($plan && $plan->getDefinition()->getIsTrialPeriodEnabled()) {
            $data['is_auto_trial_price'] = !isset($data['trial_price'])
                || $data['trial_price'] == '';
            if ($data['is_auto_trial_price']) {
                $data['trial_price'] = null;
            }
        } else {
            $data['is_auto_trial_price'] = true;
            $data['trial_price'] = null;
        }

        $data['is_auto_regular_price'] = !isset($data['regular_price'])
            || $data['regular_price'] == '';
        if ($data['is_auto_regular_price']) {
            $data['regular_price'] = null;
        }

        if ($productType == BundleType::TYPE_CODE) {
            $data['trial_price'] = null;
            $data['regular_price'] = null;
            $data['is_auto_trial_price'] = true;
            $data['is_auto_regular_price'] = true;
        }

        return $data;
    }

    /**
     * Retrieve plan by plan id
     *
     * @param $planId
     * @return PlanInterface|null
     */
    private function getPlan($planId)
    {
        try {
            $plan = $this->planRepository->get($planId);
        } catch (\Exception $exception) {
            $plan = null;
        }

        return $plan;
    }
}
