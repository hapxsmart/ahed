<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Model\PlanRepository;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TitleResolver
 * @package Aheadworks\Sarp2\Model\Plan
 */
class TitleResolver
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @param StoreManagerInterface $storeManager
     * @param PlanRepository $planRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PlanRepository $planRepository
    ) {
        $this->storeManager = $storeManager;
        $this->planRepository = $planRepository;
    }

    /**
     * Get plan title for the store specified
     *
     * @param PlanInterface|int $plan
     * @param null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTitle($plan, $storeId = null)
    {
        if (!$plan instanceof PlanInterface) {
            $plan = $this->getPlan($plan);
        }
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $planTitles = $plan->getTitles();

        $storeTitle = $this->getStoreTitle($planTitles, $storeId);
        $planTitle = $storeTitle ? $storeTitle : $plan->getName();

        return $planTitle;
    }

    /**
     * Get store title
     *
     * @param PlanTitleInterface[] $planTitles
     * @param $storeId
     * @return string|null
     */
    private function getStoreTitle($planTitles, $storeId)
    {
        $storeTitle = null;
        foreach ($planTitles as $title) {
            if ($title->getStoreId() == $storeId) {
                $storeTitle = $title->getTitle();
                break;
            }
        }

        if (!$storeTitle) {
            $storeTitle = $this->getDefaultTitle($planTitles);
        }

        return $storeTitle;
    }

    /**
     * Get default title
     *
     * @param PlanTitleInterface[] $planTitles
     * @return string|null
     */
    private function getDefaultTitle($planTitles)
    {
        $defaultTitle = null;
        foreach ($planTitles as $title) {
            if ($title->getStoreId() == Store::DEFAULT_STORE_ID) {
                $defaultTitle = $title->getTitle();
                break;
            }
        }

        return $defaultTitle;
    }

    /**
     * Retrieve plan
     *
     * @param $planId
     * @return PlanInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPlan($planId)
    {
        return $this->planRepository->get($planId);
    }
}
