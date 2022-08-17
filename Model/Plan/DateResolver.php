<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Model\Plan\Source\StartDateType;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTime as CoreDateTime;

/**
 * Class DateResolver
 *
 * Resolve the date on the subscription plan definition.
 * Resolver does not take possible problems with payments, suspense subscription, etc...
 *
 * @package Aheadworks\Sarp2\Model\Profile
 */
class DateResolver
{
    /**
     * @var CoreDateTime
     */
    private $coreDate;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @param CoreDateTime $coreDate
     * @param NextPaymentDate $nextPaymentDate
     */
    public function __construct(
        CoreDateTime $coreDate,
        NextPaymentDate $nextPaymentDate
    ) {
        $this->coreDate = $coreDate;
        $this->nextPaymentDate = $nextPaymentDate;
    }

    /**
     * Get subscription start date
     *
     * @param string $startDateType
     * @param int|null $dayOfMonth
     * @return string
     */
    public function getStartDate($startDateType, $dayOfMonth = null)
    {
        $startDate = null;
        switch ($startDateType) {
            case StartDateType::MOMENT_OF_PURCHASE:
                $startDate = $this->coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now');
                break;
            case StartDateType::LAST_DAY_OF_CURRENT_MONTH:
                $startDate = $this->coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'last day of this month');
                break;
            case StartDateType::EXACT_DAY_OF_MONTH:
                list($day, $month, $year, $hours, $minutes, $seconds) = [
                    $this->coreDate->gmtDate('d'),
                    $this->coreDate->gmtDate('m'),
                    $this->coreDate->gmtDate('Y'),
                    $this->coreDate->gmtDate('H'),
                    $this->coreDate->gmtDate('i'),
                    $this->coreDate->gmtDate('s')
                ];
                $format = '%s-%s-%s %s:%s:%s';
                if ((int)$day > $dayOfMonth) {
                    $format = '+1 month ' . $format;
                }
                $startDate = $this->coreDate->gmtDate(
                    DateTime::DATETIME_PHP_FORMAT,
                    sprintf($format, $year, $month, $dayOfMonth, $hours, $minutes, $seconds)
                );
                break;
            default:
                $startDate = $this->coreDate->gmtDate(DateTime::DATETIME_PHP_FORMAT, 'now');
                break;
        }
        return $startDate;
    }

    /**
     * Retrieve trial period starting date
     *
     * @param $subscriptionStartDate
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $skipInitialPayment
     * @return string|null
     */
    public function getTrialStartDate($subscriptionStartDate, $planDefinition, $skipInitialPayment = false) {
        $trialStartDate = $subscriptionStartDate;
        if ($planDefinition->getIsTrialPeriodEnabled()) {
            if ($skipInitialPayment && $planDefinition->getIsInitialFeeEnabled())
                $trialStartDate = $this->shiftDate(
                    $trialStartDate,
                    1,
                    $planDefinition->getTrialBillingPeriod(),
                    $planDefinition->getTrialBillingFrequency()
                );
        }

        return $trialStartDate;
    }

    /**
     * Retrieve trial period stopping date
     *
     * @param string $subscriptionStartDate
     * @param PlanDefinitionInterface $planDefinition
     * @return string|null
     */
    public function getTrialStopDate($subscriptionStartDate, $planDefinition) {
        $stopDate = $subscriptionStartDate;
        if ($planDefinition->getIsTrialPeriodEnabled()) {
            $stopDate = $this->shiftDate(
                $subscriptionStartDate,
                $planDefinition->getTrialTotalBillingCycles(),
                $planDefinition->getTrialBillingPeriod(),
                $planDefinition->getTrialBillingFrequency(),
                false
            );
        }

        return $stopDate;
    }

    /**
     * Retrieve regular period starting date
     *
     * @param string $subscriptionStartDate
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $skipInitialPayment
     * @return string|null
     */
    public function getRegularStartDate($subscriptionStartDate, $planDefinition, $skipInitialPayment = false) {
        $regularStartDate = $subscriptionStartDate;
        if ($planDefinition->getIsTrialPeriodEnabled()) {
            $regularStartDate = $this->shiftDate(
                $this->getTrialStopDate($subscriptionStartDate, $planDefinition),
                1,
                $planDefinition->getTrialBillingPeriod(),
                $planDefinition->getTrialBillingFrequency()
            );
        } elseif ($planDefinition->getIsInitialFeeEnabled() && $skipInitialPayment) {
            $regularStartDate = $this->shiftDate(
                $subscriptionStartDate,
                1,
                $planDefinition->getBillingPeriod(),
                $planDefinition->getBillingFrequency()
            );
        }

        return $regularStartDate;
    }

    /**
     * Retrieve regular period stopping date
     *
     * @param string $subscriptionStartDate
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $includeMembership
     * @return string|null
     */
    public function getRegularStopDate($subscriptionStartDate, $planDefinition, $includeMembership = true) {
        $stopDate = $this->getStopDate(
            $subscriptionStartDate,
            $planDefinition,
            $includeMembership
        );

        return $stopDate;
    }

    /**
     * Get subscription stop date
     *
     * @param string $startDate
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $includeMembership
     * @return string
     */
    public function getStopDate($startDate, $planDefinition, $includeMembership = false)
    {
        $stopDate = $startDate;

        $trialCycles = $planDefinition->getTrialTotalBillingCycles();
        $regularCycles = $planDefinition->getTotalBillingCycles();
        if (0 == $regularCycles) {
            return null;
        } elseif ($includeMembership && $planDefinition->getIsMembershipModelEnabled()) {
            $regularCycles++;
        }

        $stopDate = $this->shiftDate(
            $stopDate,
            $trialCycles,
            $planDefinition->getTrialBillingPeriod(),
            $planDefinition->getTrialBillingFrequency()
        );

        $stopDate = $this->shiftDate(
            $stopDate,
            $regularCycles,
            $planDefinition->getBillingPeriod(),
            $planDefinition->getBillingFrequency(),
            false
        );

        return $stopDate;
    }

    /**
     * Shift date on X billing period by profile definition
     *
     * @param string $date
     * @param int $cyclesCount
     * @param int $period
     * @param string $frequency
     * @return string
     */
    private function shiftDate($date, $cyclesCount, $period, $frequency, $inclusiveLastPeriod = true)
    {
        if (!$inclusiveLastPeriod) {
            $cyclesCount--;
        }
        return $this->nextPaymentDate->shiftDate(
            $date,
            $cyclesCount,
            $period,
            $frequency
        );
    }

    /**
     * Check if start date is today
     *
     * @param $startDateType
     * @param null $dayOfMonth
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isToday($startDateType, $dayOfMonth = null)
    {
        // todo: will be implemented after support of start date type different from 'moment of purchase'
        return true;
    }
}
