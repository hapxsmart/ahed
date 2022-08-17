<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option\Source;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Plan\Checker as PlanChecker;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Finder as SubscriptionOptionFinder;

class Backend
{
    /**
     * @var SubscriptionOptionFinder
     */
    private $subscriptionOptionFinder;

    /**
     * @var PlanChecker
     */
    private $planChecker;

    /**
     * @param SubscriptionOptionFinder $subscriptionOptionFinder
     * @param PlanChecker $planChecker
     */
    public function __construct(
        SubscriptionOptionFinder $subscriptionOptionFinder,
        PlanChecker $planChecker
    ) {
        $this->subscriptionOptionFinder = $subscriptionOptionFinder;
        $this->planChecker = $planChecker;
    }

    /**
     * Get backend options for plan selection
     *
     * @param int $productId
     * @return array
     * @throws LocalizedException
     */
    public function getPlanOptionArray($productId)
    {
        $optionArray = [];
        $options = $this->subscriptionOptionFinder->getSortedOptions($productId);
        foreach ($options as $option) {
            if ($this->planChecker->isEnabled($option->getPlanId())) {
                $optionArray[$option->getPlanId()] = $option->getBackendTitle();
            }
        }

        return $optionArray;
    }
}
