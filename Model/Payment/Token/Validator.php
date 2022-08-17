<?php
namespace Aheadworks\Sarp2\Model\Payment\Token;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Helper\Validator\EmptyValidator;
use Magento\Framework\Validator\AbstractValidator;

/**
 * Class Validator
 * @package Aheadworks\Sarp2\Model\Payment\Token
 */
class Validator extends AbstractValidator
{
    /**
     * @var EmptyValidator
     */
    private $emptyValidator;

    /**
     * @param EmptyValidator $emptyValidator
     */
    public function __construct(EmptyValidator $emptyValidator)
    {
        $this->emptyValidator = $emptyValidator;
    }

    /**
     * Returns true if and only if payment token entity meets the validation requirements
     *
     * @param PaymentTokenInterface $token
     * @return bool
     */
    public function isValid($token)
    {
        $this->_clearMessages();

        if ($this->emptyValidator->isValid($token->getPaymentMethod())) {
            $this->_addMessages(['Payment method is required.']);
        }
        if ($this->emptyValidator->isValid($token->getType())) {
            $this->_addMessages(['Token type is required.']);
        }
        if ($this->emptyValidator->isValid($token->getTokenValue())) {
            $this->_addMessages(['Token value is required.']);
        }

        return empty($this->getMessages());
    }
}
