<?php
namespace Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile;

use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Engine\LoggerInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class StatusHandler
 * @package Aheadworks\Sarp2\Engine\Payment\Processor\State\Profile
 */
class StatusHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handle profile status
     *
     * @param PaymentInterface $payment
     * @return PaymentInterface
     */
    public function handle($payment)
    {
        $schedule = $payment->getSchedule();
        $profile = $payment->getProfile();

        $trialCount = $schedule->getTrialCount();
        $trialTotalCount = $schedule->getTrialTotalCount();
        $regularCount = $schedule->getRegularCount();
        $regularTotalCount = $schedule->getRegularTotalCount();
        $membershipCount = $schedule->getMembershipCount();
        $membershipTotalCount = $schedule->getMembershipTotalCount();

        $isStatusUpdated = false;
        if ($profile->getStatus() != Status::ACTIVE
            && ($schedule->isInitialPaid() || ($trialTotalCount > 0 && $trialCount > 0) || $regularCount > 0)
        ) {
            $profile->setStatus(Status::ACTIVE);
            $isStatusUpdated = true;
        }
        if ($regularTotalCount > 0
            && ($trialCount + $regularCount + $membershipCount) >=
            ($trialTotalCount + $regularTotalCount + $membershipTotalCount)
        ) {
            $profile->setStatus(Status::EXPIRED);
            $isStatusUpdated = true;
        }

        if ($isStatusUpdated) {
            $this->logger->traceProcessing(
                LoggerInterface::ENTRY_PROFILE_SET_STATUS,
                ['payment' => $payment],
                ['profile' => $profile]
            );
        }

        return $payment;
    }
}
