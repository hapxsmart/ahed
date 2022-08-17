<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface as PlanDefinition;
use Aheadworks\Sarp2\Api\Data\PlanInterface as Plan;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class Definition
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class Definition implements ProcessorInterface
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(BooleanUtils $booleanUtils)
    {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareEntityData($data)
    {
        if (isset($data['definition'])) {
            $definition = $data['definition'];

            if (empty($definition[PlanDefinition::TOTAL_BILLING_CYCLES])) {
                $definition[PlanDefinition::TOTAL_BILLING_CYCLES] = 0;
            }
            if ($definition[PlanDefinition::IS_INITIAL_FEE_ENABLED] == '') {
                $definition[PlanDefinition::IS_INITIAL_FEE_ENABLED] = false;
            }
            if ($definition[PlanDefinition::IS_TRIAL_PERIOD_ENABLED] == '') {
                $definition[PlanDefinition::IS_TRIAL_PERIOD_ENABLED] = false;
            }

            $isTrialEnabled = $this->booleanUtils->toBoolean($definition[PlanDefinition::IS_TRIAL_PERIOD_ENABLED]);
            if (!$isTrialEnabled) {
                $definition[PlanDefinition::TRIAL_TOTAL_BILLING_CYCLES] = 0;
                $definition[PlanDefinition::TRIAL_BILLING_PERIOD] = null;
                $definition[PlanDefinition::TRIAL_BILLING_FREQUENCY] = null;
                $definition[PlanDefinition::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET] = null;
                $data[Plan::TRIAL_PRICE_PATTERN_PERCENT] = 0;
            }

            $data['definition'] = $definition;
        }
        return $data;
    }
}
