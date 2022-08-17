<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Validation;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Validation
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var string
     */
    private $message;

    /**
     * @param bool $isValid
     * @param string $message
     */
    public function __construct($isValid, $message = '')
    {
        $this->isValid = $isValid;
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }
}
