<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;

/**
 * Class Detector
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Outstanding
 */
class Detector
{
    /**
     * @var DetectResultFactory
     */
    private $resultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DetectResultFactory $resultFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        DetectResultFactory $resultFactory,
        LoggerInterface $logger
    ) {
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
    }

    /**
     * Detect outstanding payments.
     * Only iterates through payments with type different from 'outstanding'
     *
     * @param PaymentInterface[] $payments
     * @return DetectResult
     */
    public function detect($payments)
    {
        $todayPayments = [];
        $outstandingPayments = [];

        $today = (new \DateTime())->setTime(0, 0, 0);
        foreach ($payments as $payment) {
            $paymentType = $payment->getType();
            $baseDate = $paymentType == PaymentInterface::TYPE_REATTEMPT
                ? $payment->getRetryAt()
                : $payment->getScheduledAt();
            $base = (new \DateTime($baseDate))->setTime(0, 0, 0);

            if ($paymentType != PaymentInterface::TYPE_OUTSTANDING && $base < $today) {
                $outstandingPayments[] = $payment;
            } else {
                $todayPayments[] = $payment;
            }
        }

        if (count($outstandingPayments)) {
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_OUTSTANDING_PAYMENTS_DETECTED,
                ['payments' => $payments],
                ['outstandingPayments' => $outstandingPayments]
            );
        }

        return $this->resultFactory->create(
            [
                'todayPayments' => $todayPayments,
                'outstandingPayments' => $outstandingPayments
            ]
        );
    }
}
