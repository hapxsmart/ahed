<?php
namespace Aheadworks\Sarp2\Engine\Payment\Schedule;

use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;

/**
 * Class ValueResolver
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Schedule
 */
class ValueResolver
{
    /**
     * @var Checker
     */
    private $scheduleChecker;

    /**
     * @param Checker $scheduleChecker
     */
    public function __construct(
        Checker $scheduleChecker
    ) {
        $this->scheduleChecker = $scheduleChecker;
    }

    /**
     * Resolve schedule period
     *
     * @param ScheduleInterface $schedule
     * @return string
     */
    public function getPeriod($schedule)
    {
        return $this->isTrial($schedule)
            ? $schedule->getTrialPeriod()
            : $schedule->getPeriod();
    }

    /**
     * Resolve schedule frequency
     *
     * @param ScheduleInterface $schedule
     * @return int
     */
    public function getFrequency($schedule)
    {
        return $this->isTrial($schedule)
            ? $schedule->getTrialFrequency()
            : $schedule->getFrequency();
    }

    /**
     * Check if trial payment period
     *
     * @param ScheduleInterface $schedule
     * @return bool
     */
    private function isTrial($schedule)
    {
        return $this->scheduleChecker->isTrialNextPayment($schedule)
                || ($this->scheduleChecker->isFirstRegularNextPayment($schedule)
                    && $this->scheduleChecker->isTrialPeriodEnable($schedule)
                );
    }
}
