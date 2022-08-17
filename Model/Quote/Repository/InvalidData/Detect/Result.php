<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isInvalid;

    /**
     * @var int|null
     */
    private $reason;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param bool $isInvalid
     * @param int|null $reason
     * @param string $errorMessage
     */
    public function __construct(
        $isInvalid,
        $reason = null,
        $errorMessage = ''
    ) {
        $this->isInvalid = $isInvalid;
        $this->reason = $reason;
        $this->errorMessage = $errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function isInvalid()
    {
        return $this->isInvalid;
    }

    /**
     * {@inheritdoc}
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
