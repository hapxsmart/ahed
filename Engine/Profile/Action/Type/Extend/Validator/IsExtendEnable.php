<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator;

use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class IsExtendEnable
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator
 */
class IsExtendEnable extends AbstractValidator
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        $profileDefinition = $profile->getProfileDefinition();
        if (!$profileDefinition->getIsExtendEnable()) {
            $this->addMessages(['The Extend action is not possible for this subscription.']);
        }
    }
}
