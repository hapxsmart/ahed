<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SortOrderResolver
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Option
 */
class SortOrderResolver
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        PlanRepositoryInterface $planRepository
    ) {
        $this->planRepository = $planRepository;
    }

    /**
     * Get sort order
     *
     * @param SubscriptionOptionInterface $option
     * @return int|null
     */
    public function getSortOrder($option)
    {
        try {
            $plan = $this->planRepository->get($option->getPlanId());
            $result = $plan->getSortOrder();
        } catch (LocalizedException $e) {
            $result = null;
        }

        return $result;
    }
}
