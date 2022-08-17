<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeAddress\Validator;

use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class IsVirtualProfile
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeAddress\Validator
 */
class IsVirtualProfile extends AbstractValidator
{
    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        if ($profile->getIsVirtual()) {
            $this->addMessages(['You can\'t change the shipping address in the virtual profile.']);
        }
    }
}
