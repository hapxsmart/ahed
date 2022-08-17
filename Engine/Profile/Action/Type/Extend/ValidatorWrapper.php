<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionFactory;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;

/**
 * Class ValidatorWrapper
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend
 */
class ValidatorWrapper
{
    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @param ValidatorComposite $validator
     * @param ActionFactory $actionFactory
     */
    public function __construct(
        ValidatorComposite $validator,
        ActionFactory $actionFactory
    ) {
        $this->validator = $validator;
        $this->actionFactory = $actionFactory;
    }

    /**
     * Is valid
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isValid($profile)
    {
        $action = $this->actionFactory->create([
            'type' => ActionInterface::ACTION_TYPE_EXTEND
        ]);

        return $this->validator->isValid($profile, $action);
    }
}
