<?php
namespace Aheadworks\Sarp2\Engine\Payment\Schedule;

use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;

/**
 * Class Checker
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Schedule
 */
class Checker
{
    /**
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isTrialPeriodEnable(ScheduleInterface $schedule)
    {
        return $schedule->getTrialTotalCount() > 0;
    }

    /**
     * Check if finite subscription
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isFiniteSubscription(ScheduleInterface $schedule)
    {
        return $schedule->getRegularTotalCount() > 0;
    }

    /**
     * Check if next payment is trial
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isTrialNextPayment(ScheduleInterface $schedule)
    {
        return $this->isTrialPeriodEnable($schedule)
               && $schedule->getTrialCount() < $schedule->getTrialTotalCount();
    }

    /**
     * Check if next payment is trial
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isRegularNextPayment(ScheduleInterface $schedule)
    {
        return !$this->isTrialNextPayment($schedule)
            && $schedule->getRegularCount() != $schedule->getRegularTotalCount();
    }

    /**
     * Check if next payment is first regular
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isFirstRegularNextPayment(ScheduleInterface $schedule)
    {
        return !$this->isTrialNextPayment($schedule)
            && $schedule->getRegularCount() == 0;
    }

    /**
     * Check if no next regular payment
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isNoNextRegularPayment(ScheduleInterface $schedule)
    {
        return $schedule->getRegularCount() == $schedule->getRegularTotalCount();
    }

    /**
     * Check if next payment is final
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isLastNextPayment(ScheduleInterface $schedule)
    {
        return $this->isRegularNextPayment($schedule)
            && $schedule->getRegularTotalCount() - $schedule->getRegularCount() == 1;
    }

    /**
     * Check if next payment is membership
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isMembershipNextPayment(ScheduleInterface $schedule)
    {
        return $this->isNoNextRegularPayment($schedule)
            && $schedule->getMembershipCount() < $schedule->getMembershipTotalCount();
    }

    /**
     * Check if no payments
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    public function isNoPayments(ScheduleInterface $schedule)
    {
        return $schedule->getTrialCount() + $schedule->getRegularCount() < 1;
    }
}
