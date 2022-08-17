<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Interface GeneratorInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface GeneratorInterface
{
    /**
     * Generate payments.
     * Assumed that this include only planning of payments on particular time.
     * This include:
     * - profile creation -> first planned payments;
     * - actual payments paid -> next planned payments;
     * - actual payments failed -> planned reattempts
     *
     * @param SourceInterface $source
     * @return PaymentInterface[]
     */
    public function generate(SourceInterface $source);
}
