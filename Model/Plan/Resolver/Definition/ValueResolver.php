<?php
namespace Aheadworks\Sarp2\Model\Plan\Resolver\Definition;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Model\Config;

/**
 * Class ValueResolver
 *
 * @package Aheadworks\Sarp2\Model\Plan\Definition
 */
class ValueResolver
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
     * Retrieve email reminder offset
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param int $storeId
     * @return int
     */
    public function getUpcomingEmailOffset($planDefinition, $storeId)
    {
        $offset = $planDefinition->getIsTrialPeriodEnabled()
            ? $planDefinition->getUpcomingTrialBillingEmailOffset()
            : $planDefinition->getUpcomingBillingEmailOffset();

        if (!$offset) {
            $offset = $this->config->getUpcomingBillingEmailOffset($storeId);
        }

        return $offset;
    }
}
