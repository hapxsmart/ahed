<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface as Definition;

/**
 * Class CanCancelSubscription
 *
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class CanCancelSubscription implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function prepareEntityData($data)
    {
        if (isset($data['use_default'][Definition::IS_ALLOW_SUBSCRIPTION_CANCELLATION])
            && (bool)(int)$data['use_default'][Definition::IS_ALLOW_SUBSCRIPTION_CANCELLATION]
        ) {
            $data['definition'][Definition::IS_ALLOW_SUBSCRIPTION_CANCELLATION] = null;
        }

        return $data;
    }
}
