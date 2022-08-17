<?php
namespace Aheadworks\Sarp2\Model\Plan\DataProvider\Processor;

use Aheadworks\Sarp2\Api\Data\PlanInterface;

/**
 * Class BillingCycles
 *
 * @package Aheadworks\Sarp2\Model\Plan\DataProvider\Processor
 */
class BillingCycles implements ProcessorInterface
{
    /**
     * @inheritDoc
     */
    public function process($data)
    {
        if (!isset($data[PlanInterface::PLAN_ID])) {
            return $data;
        }

        if ($data['definition']['trial_total_billing_cycles'] == 0) {
            $data['definition']['trial_total_billing_cycles'] = null;
        }

        return $data;
    }
}
