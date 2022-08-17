<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Validation;

/**
 * Class ValidatorComposite
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Validation
 */
class ValidatorComposite extends AbstractValidator
{
    /**
     * @var AbstractValidator[]
     */
    private $validators;

    /**
     * @param array $validators
     */
    public function __construct(
        array $validators = []
    ) {
        $this->validators = $validators;
    }

    /**
     * @inheritDoc
     */
    protected function performValidation($profile, $action)
    {
        foreach ($this->validators as $validator) {
            try {
                if (!$validator->isValid($profile, $action)) {
                    $this->addMessages($validator->getMessages());
                }
            } catch (\Exception $exception) {
                $this->addMessages([$exception->getMessage()]);
            }
        }
    }

    /**
     * Retrieve first message
     *
     * @return string
     */
    public function getMessage()
    {
        $messages = $this->getMessages();

        return reset($messages);
    }
}
