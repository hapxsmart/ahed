<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Copy;

/**
 * Class Generator
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type\Planned\Bundle
 */
class Generator
{
    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var Copy
     */
    private $copyService;

    /**
     * @param PaymentFactory $paymentFactory
     * @param Copy $copyService
     */
    public function __construct(
        PaymentFactory $paymentFactory,
        Copy $copyService
    ) {
        $this->paymentFactory = $paymentFactory;
        $this->copyService = $copyService;
    }

    /**
     * Generate bundled payment using base payment
     *
     * @param Payment[] $basePayments
     * @return Payment
     */
    public function generate($basePayments)
    {
        /** @var Payment $bundledPayment */
        $bundledPayment = $this->paymentFactory->create();
        $this->copyService->copyToBundled($basePayments[0], $bundledPayment);

        $totalScheduled = 0.0;
        $baseTotalScheduled = 0.0;
        foreach ($basePayments as $payment) {
            $totalScheduled += $payment->getTotalScheduled();
            $baseTotalScheduled += $payment->getBaseTotalScheduled();
        }
        $bundledPayment->setTotalScheduled($totalScheduled)
            ->setBaseTotalScheduled($baseTotalScheduled);

        return $bundledPayment;
    }
}
