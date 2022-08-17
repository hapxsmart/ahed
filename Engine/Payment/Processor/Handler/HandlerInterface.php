<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Handler;

use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Interface HandlerInterface
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Handler
 */
interface HandlerInterface
{
    /**
     * Process payment
     *
     * @param PaymentInterface $payment
     * @return void
     */
    public function handle(PaymentInterface $payment);
}
