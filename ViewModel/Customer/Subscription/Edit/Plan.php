<?php
namespace Aheadworks\Sarp2\ViewModel\Customer\Subscription\Edit;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class Plan
 * @package Aheadworks\Sarp2\ViewModel\Customer\Subscription\Edit
 */
class Plan implements ArgumentInterface
{
    /**
     * Get subscription details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getSubscriptionDetails($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Installment details')
            : __('Subscription details');
    }

    /**
     * Get button title
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getButtonTitle($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Save Installment')
            : __('Save Subscription');
    }
}
