<?php
namespace Aheadworks\Sarp2\Model\Plan\PostDataProcessor;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface as Definition;

/**
 * Class OfferExtend
 *
 * @package Aheadworks\Sarp2\Model\Plan\PostDataProcessor
 */
class OfferExtend implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function prepareEntityData($data)
    {
        if (isset($data['definition'][Definition::TOTAL_BILLING_CYCLES])
            && (int)$data['definition'][Definition::TOTAL_BILLING_CYCLES] < 1
        ) {
            $data['definition'][Definition::IS_EXTEND_ENABLE] = 0;
            $data['definition'][Definition::OFFER_EXTEND_EMAIL_OFFSET] = null;
            $data['definition'][Definition::OFFER_EXTEND_EMAIL_TEMPLATE] = null;
        }

        if (isset($data['definition'][Definition::IS_EXTEND_ENABLE])
            && (int)$data['definition'][Definition::IS_EXTEND_ENABLE] < 1
        ) {
            $data['definition'][Definition::OFFER_EXTEND_EMAIL_OFFSET] = null;
            $data['definition'][Definition::OFFER_EXTEND_EMAIL_TEMPLATE] = null;
        }

        return $data;
    }
}
