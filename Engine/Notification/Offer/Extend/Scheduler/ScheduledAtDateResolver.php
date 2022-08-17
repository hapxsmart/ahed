<?php
namespace Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface as ScheduledPaymentInfo;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Profile\PaymentsInfo\ProviderInterface;
use Aheadworks\Sarp2\Model\Plan\DateResolver;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class ScheduledAtDateResolver
 *
 * @package Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Scheduler
 */
class ScheduledAtDateResolver
{
    /**
     * @var DateResolver
     */
    private $profileDateResolver;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var ProviderInterface
     */
    private $paymentInfoProvider;

    /**
     * @param DateResolver $profileDateResolver
     * @param PaymentsList $paymentsList
     * @param ProviderInterface $paymentInfoProvider
     */
    public function __construct(
        DateResolver $profileDateResolver,
        PaymentsList $paymentsList,
        ProviderInterface $paymentInfoProvider
    ) {
        $this->profileDateResolver = $profileDateResolver;
        $this->paymentsList = $paymentsList;
        $this->paymentInfoProvider = $paymentInfoProvider;
    }

    /**
     * Retrieve scheduled at date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Exception
     */
    public function getScheduledAtDate($profile)
    {
        $lastPaymentDay = $this->getLastPaymentDate($profile);
        $scheduledAt = new \DateTime($lastPaymentDay);

        $offset = (int)$profile->getProfileDefinition()->getOfferExtendEmailOffset();
        if ($offset > 1) {
            $scheduledAt->modify('+' . $offset . ' day');
        } elseif ($offset < 0) {
            $scheduledAt->modify('-' . abs($offset) . ' day');
        }

        return $scheduledAt;
    }

    /**
     * Retrieve last payment date
     *
     * @param ProfileInterface $profile
     * @return string
     */
    private function getLastPaymentDate($profile)
    {
        if ($profile->getStatus() == Status::EXPIRED || $this->isLastPeriodHolder($profile)) {
            return $profile->getLastOrderDate();
        } else {
            $scheduledPayments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            if (!empty($scheduledPayments)) {
                $payment = reset($scheduledPayments);
                return $payment->getScheduledAt();
            }
        }

        return $this->profileDateResolver->getStopDate(
            $profile->getStartDate(),
            $profile->getProfileDefinition()
        );
    }

    /**
     * Check if next payment status is last period holder
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    private function isLastPeriodHolder($profile)
    {
        $scheduledInfo = $this->paymentInfoProvider->getScheduledPaymentsInfo($profile->getProfileId());
        return $scheduledInfo->getPaymentStatus() == ScheduledPaymentInfo::PAYMENT_STATUS_LAST_PERIOD_HOLDER;
    }
}
