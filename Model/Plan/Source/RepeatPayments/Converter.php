<?php
namespace Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments;

use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod as BillingPeriodSource;
use Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments as RepeatPaymentsSource;

/**
 * Class Converter
 * @package Aheadworks\Sarp2\Model\Plan\Source\RepeatPayments
 */
class Converter
{
    /**
     * Get repeat payments value using billing period and billing frequency
     *
     * @param string $billingPeriod
     * @param int $billingFrequency
     * @return int|null
     */
    public function toRepeatPayments($billingPeriod, $billingFrequency)
    {
        if ($billingFrequency == 1) {
            if ($billingPeriod == BillingPeriodSource::DAY) {
                return RepeatPaymentsSource::DAILY;
            }
            if ($billingPeriod == BillingPeriodSource::WEEK) {
                return RepeatPaymentsSource::WEEKLY;
            }
            if ($billingPeriod == BillingPeriodSource::MONTH) {
                return RepeatPaymentsSource::MONTHLY;
            }
            if ($billingPeriod == BillingPeriodSource::YEAR) {
                return RepeatPaymentsSource::YEARLY;
            }
        }
        return null;
    }

    /**
     * Get billing period value using repeat payments
     *
     * @param int $repeatPayments
     * @return null|string
     */
    public function toBillingPeriod($repeatPayments)
    {
        if ($repeatPayments == RepeatPaymentsSource::DAILY) {
            return BillingPeriodSource::DAY;
        }
        if ($repeatPayments == RepeatPaymentsSource::WEEKLY) {
            return BillingPeriodSource::WEEK;
        }
        if ($repeatPayments == RepeatPaymentsSource::MONTHLY) {
            return BillingPeriodSource::MONTH;
        }
        if ($repeatPayments == RepeatPaymentsSource::YEARLY) {
            return BillingPeriodSource::YEAR;
        }
        return null;
    }

    /**
     * Get billing frequency value using repeat payments
     *
     * @param int $repeatPayments
     * @return int|null
     */
    public function toBillingFrequency($repeatPayments)
    {
        return in_array(
            $repeatPayments,
            [
                RepeatPaymentsSource::DAILY,
                RepeatPaymentsSource::WEEKLY,
                RepeatPaymentsSource::MONTHLY,
                RepeatPaymentsSource::YEARLY,
            ]
        )
            ? 1
            : null;
    }
}
