<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\Type;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\ProcessorInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\Process\ResultFactory;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\PaymentType\Resolver as PaymentTypeResolver;
use Aheadworks\Sarp2\Engine\Payment\Processor\Type\Outstanding\Reason\Resolver as ReasonResolver;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Outstanding
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\Type
 */
class Outstanding implements ProcessorInterface
{
    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var IsProcessable
     */
    private $isProcessableChecker;

    /**
     * @var ReasonResolver
     */
    private $reasonResolver;

    /**
     * @var PaymentTypeResolver
     */
    private $paymentTypeResolver;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ValueResolver
     */
    private $schedulePeriodValueResolver;

    /**
     * @param Persistence $persistence
     * @param IsProcessable $isProcessableChecker
     * @param ReasonResolver $reasonResolver
     * @param PaymentTypeResolver $paymentTypeResolver
     * @param NextPaymentDate $nextPaymentDate
     * @param DateTime $dateTime
     * @param ResultFactory $resultFactory
     * @param LoggerInterface $logger
     * @param ValueResolver $periodValueResolver
     */
    public function __construct(
        Persistence $persistence,
        IsProcessable $isProcessableChecker,
        ReasonResolver $reasonResolver,
        PaymentTypeResolver $paymentTypeResolver,
        NextPaymentDate $nextPaymentDate,
        DateTime $dateTime,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        ValueResolver $periodValueResolver
    ) {
        $this->persistence = $persistence;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->reasonResolver = $reasonResolver;
        $this->paymentTypeResolver = $paymentTypeResolver;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->dateTime = $dateTime;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->schedulePeriodValueResolver = $periodValueResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process($payments)
    {
        $payments = array_filter($payments, [$this, 'isProcessable']);

        /** @var Payment $payment */
        foreach ($payments as $payment) {
            $schedule = $payment->getSchedule();
            $paymentStatus = $payment->getPaymentStatus();

            $reason = $this->reasonResolver->getReason($payment);
            $baseDate = $paymentStatus == PaymentInterface::STATUS_RETRYING
                ? $payment->getRetryAt()
                : $payment->getScheduledAt();
            $rescheduleDate = $reason == ReasonResolver::REASON_REACTIVATED
                ? $this->nextPaymentDate->getDateNextForOutstanding(
                    $baseDate,
                    $this->schedulePeriodValueResolver->getPeriod($schedule),
                    $this->schedulePeriodValueResolver->getFrequency($schedule)
                )
                : $this->dateTime->formatDate(true);

            $payment->setType($this->paymentTypeResolver->getPaymentType($payment));
            if ($paymentStatus == PaymentInterface::STATUS_RETRYING) {
                $payment->setRetryAt($rescheduleDate);
            } else {
                $payment->setScheduledAt($rescheduleDate);
            }
            if ($reason == ReasonResolver::REASON_REACTIVATED) {
                $schedule->setIsReactivated(false);
            }

            try {
                $this->persistence->save($payment);

                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_PAYMENT_UPDATE,
                    ['payments' => $payments],
                    ['updatedPayment' => $payment]
                );
            } catch (\Exception $e) {
                $this->logger->traceProcessing(
                    LoggerInterface::ENTRY_PAYMENT_UPDATE_FAILED,
                    ['payments' => $payments],
                    [
                        'updatedPayment' => $payment,
                        'exception' => $e
                    ]
                );
            }
        }

        return $this->resultFactory->create();
    }

    /**
     * Check if payment is processable
     *
     * @param $payment
     * @return bool
     */
    private function isProcessable($payment)
    {
        return $this->isProcessableChecker->check($payment, PaymentInterface::TYPE_OUTSTANDING);
    }
}
