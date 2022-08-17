<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface as Definition;

/**
 * Class Reminder
 *
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class Reminder implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function prepareEntityData($data)
    {
        if (isset($data['use_default'][Definition::UPCOMING_BILLING_EMAIL_OFFSET])
            && (bool)$data['use_default'][Definition::UPCOMING_BILLING_EMAIL_OFFSET]
        ) {
            $data['definition'][Definition::UPCOMING_BILLING_EMAIL_OFFSET] = null;
        }

        if (isset($data['use_default'][Definition::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET])
            && (bool)$data['use_default'][Definition::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET]
        ) {
            $data['definition'][Definition::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET] = null;
        }

        return $data;
    }
}
