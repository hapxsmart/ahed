<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\Profile;

use Aheadworks\Sarp2\Model\Profile\Validator;

/**
 * Class ValidatorStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\Profile
 */
class ValidatorStub extends Validator
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isValid($profile)
    {
        return true;
    }
}
