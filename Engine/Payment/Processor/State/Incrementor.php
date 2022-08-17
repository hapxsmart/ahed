<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\State;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile\StatusHandler;

/**
 * Class Incrementor
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\State
 */
class Incrementor
{
    /**
     * @var StatusHandler
     */
    private $profileStatusHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StatusHandler $profileStatusHandler
     * @param LoggerInterface $logger
     */
    public function __construct(
        StatusHandler $profileStatusHandler,
        LoggerInterface $logger
    ) {
        $this->profileStatusHandler = $profileStatusHandler;
        $this->logger = $logger;
    }

    /**
     * Increment state
     *
     * @param PaymentInterface $payment
     * @param bool $handleProfileStatus
     */
    public function increment($payment, $handleProfileStatus = true)
    {
        if ($payment->isBundled()) {
            foreach ($payment->getChildItems() as $child) {
                $this->increment($child, $handleProfileStatus);
            }
        } else {
            $paymentPeriod = $payment->getPaymentPeriod();
            $paymentType = $payment->getType();

            $schedule = $payment->getSchedule();

            if ($paymentType == PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
                $schedule->setMembershipCount($schedule->getMembershipCount() + 1);
            } else {
                if ($paymentPeriod == PaymentInterface::PERIOD_INITIAL) {
                    $schedule->setIsInitialPaid(true);
                    if ($schedule->getTrialTotalCount() > 0) {
                        $schedule->setTrialCount($schedule->getTrialCount() + 1);
                    } else {
                        $schedule->setRegularCount($schedule->getRegularCount() + 1);
                    }
                } elseif ($paymentPeriod == PaymentInterface::PERIOD_TRIAL) {
                    $schedule->setTrialCount($schedule->getTrialCount() + 1);
                } else {
                    $schedule->setRegularCount($schedule->getRegularCount() + 1);
                }
            }

            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_INCREMENT_STATE,
                ['payment' => $payment],
                ['schedule' => $schedule]
            );

            if ($handleProfileStatus) {
                $this->profileStatusHandler->handle($payment);
            }
        }

        $payment->setRetriesCount(0);
    }
}
