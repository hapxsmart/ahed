<?php
namespace Aheadworks\Sarp2\Model\Config;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Model\Config;

/**
 * Class CanCancelSubscriptionValueResolver
 *
 * @package Aheadworks\Sarp2\Model\Config
 */
class CanCancelSubscriptionValueResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve boolean flag that determines availability of cancel subscription
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return bool
     */
    public function canCancelSubscription($planDefinition)
    {
        $isAllowCancelSubscription = $planDefinition->getIsAllowSubscriptionCancellation();
        if (null === $isAllowCancelSubscription) {
            $isAllowCancelSubscription = $this->config->canCancelSubscription();
        }

        return (bool)$isAllowCancelSubscription;
    }
}
