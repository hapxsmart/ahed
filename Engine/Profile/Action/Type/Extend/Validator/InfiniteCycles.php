<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator;

use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class InfiniteCycles
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator
 */
class InfiniteCycles extends AbstractValidator
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        $profileDefinition = $profile->getProfileDefinition();
        if ($profileDefinition->getTotalBillingCycles() < 1) {
            $this->addMessages(['This is infinite subscription.']);
        }
    }
}
