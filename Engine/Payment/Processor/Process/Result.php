<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Process;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Process
 */
class Result implements ResultInterface
{
    /**
     * @var bool
     */
    private $isOutstandingDetected;

    /**
     * @param bool $isOutstandingDetected
     */
    public function __construct($isOutstandingDetected = false)
    {
        $this->isOutstandingDetected = $isOutstandingDetected;
    }

    /**
     * {@inheritdoc}
     */
    public function isOutstandingDetected()
    {
        return $this->isOutstandingDetected;
    }
}
