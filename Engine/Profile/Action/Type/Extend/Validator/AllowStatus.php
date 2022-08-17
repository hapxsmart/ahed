<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator;

use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class StatusValidator
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator
 */
class AllowStatus extends AbstractValidator
{
    /**
     * @var array
     */
    private $allowStatuses = [
        StatusSource::ACTIVE,
        StatusSource::EXPIRED
    ];

    /**
     * @param array $allowStatuses
     */
    public function __construct(
        array $allowStatuses = []
    ) {
        $this->allowStatuses = array_merge($this->allowStatuses, $allowStatuses);
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        if (!in_array($profile->getStatus(), $this->allowStatuses)) {
            $this->addMessages(['The subscription status in not correct for perform extend action.']);
        }
    }
}
