<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Process;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Process
 */
interface ResultInterface
{
    /**
     * Check if outstanding payments detected
     *
     * @return bool
     */
    public function isOutstandingDetected();
}
