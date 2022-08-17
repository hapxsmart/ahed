<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultInterface;

/**
 * Interface ProcessorInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface ProcessorInterface
{
    /**
     * Process payments
     *
     * @param PaymentInterface[] $payments
     * @return ResultInterface
     */
    public function process($payments);
}
