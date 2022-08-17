<?php
namespace Aheadworks\Sarp2\Model\Email\Send;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Model\Email\Send
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isSuccessful;

    /**
     * @var bool
     */
    private $isDisabled;

    /**
     * @param bool $isSuccessful
     * @param bool $isDisabled
     */
    public function __construct(
        $isSuccessful,
        $isDisabled
    ) {
        $this->isSuccessful = $isSuccessful;
        $this->isDisabled = $isDisabled;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessful()
    {
        return $this->isSuccessful;
    }

    /**
     * {@inheritdoc}
     */
    public function isDisabled()
    {
        return $this->isDisabled;
    }
}
