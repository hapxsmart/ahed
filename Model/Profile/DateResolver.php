<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker as ScheduleChecker;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\CollectionFactory;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class DateResolver
 *
 * Resolve the date on the existing subscription profile adjusted for failed payments.
 *
 * @package Aheadworks\Sarp2\Model\Profile
 */
class DateResolver
{
    /**
     * @var Persistence
     */
    private $schedulePersistence;
    
    
    private $scheduleChecker;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param Persistence $schedulePersistence
     * @param ScheduleChecker $scheduleChecker
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentsList $paymentsList
     * @param CollectionFactory $collectionFactory
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        Persistence $schedulePersistence,
        ScheduleChecker $scheduleChecker,
        NextPaymentDate $nextPaymentDate,
        PaymentsList $paymentsList,
        CollectionFactory $collectionFactory,
        Factory $dataObjectFactory
    ) {
        $this->profileRepository = $profileRepository;
        $this->schedulePersistence = $schedulePersistence;
        $this->scheduleChecker = $scheduleChecker;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->paymentsList = $paymentsList;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get profile start date
     *
     * @param int $profileId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStartDate($profileId)
    {
        return $this->getProfile($profileId)->getCreatedAt();
    }

    /**
     * Retrieve initial period starting date
     * 
     * @param int $profileId
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getInitialStartDate($profileId) {
        $profile = $this->getProfile($profileId);
        $startDate = $profile->getStartDate();

        $schedule = $this->getSchedule($profileId);
        $filter = $this->createFilter('payment_period', ['in' => [
            PaymentInterface::PERIOD_INITIAL,
            PaymentInterface::PERIOD_TRIAL,
            PaymentInterface::PERIOD_REGULAR
        ]]);

        if ($this->scheduleChecker->isNoPayments($schedule)) {
            $firstScheduledOrPaidPaymentDate = $this->getFirstScheduledOrPaidPaymentDate($profileId, $filter);
            if ($firstScheduledOrPaidPaymentDate) {
                $startDate = $firstScheduledOrPaidPaymentDate;
            }
        } else {
            $firstPaid = $this->paymentsList->getFirstPaid($profileId, $filter);
            if ($firstPaid->getId()) {
                $startDate = $firstPaid->getPaidAt();
            }
        }

        return $startDate;
    }

    /**
     * Retrieve trial period starting date
     *
     * @param string $profileId
     * @param bool $skipInitialPayment
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTrialStartDate($profileId, $skipInitialPayment = false) {
        $profile = $this->getProfile($profileId);
        $profileDefinition = $profile->getProfileDefinition();
        $startDate = $profile->getStartDate();

        if ($profileDefinition->getIsTrialPeriodEnabled()
            && ($skipInitialPayment && $profileDefinition->getIsInitialFeeEnabled())
        ) {
            $schedule = $this->getSchedule($profileId);
            if ($schedule) {
                $filter = $this->createFilter('payment_period', ['eq' => PaymentInterface::PERIOD_TRIAL]);
                if ($schedule->getTrialTotalCount() > 1) {
                    if ($schedule->getTrialCount() == 1) {
                        // retrieve date from 2nd scheduled trial payment
                        $lastScheduledPaymentDate = $this->getLastScheduledPaymentDate($profileId, $filter);
                        if ($lastScheduledPaymentDate) {
                            $startDate = $lastScheduledPaymentDate;
                        }
                    } elseif ($schedule->getTrialCount() > 1) {
                        // retrieve date from 2nd paid trial payment
                        $secondPaid = $this->paymentsList->getFirstPaid($profileId, $filter);
                        if ($secondPaid->getId()) {
                            $startDate = $secondPaid->getPaidAt();
                        }
                    } else {
                        $startDate = $this->shiftDate(
                            $startDate,
                            1,
                            $profileDefinition->getTrialBillingPeriod(),
                            $profileDefinition->getTrialBillingFrequency()
                        );
                    }
                }
            }
        }

        return $startDate;
    }

    /**
     * Retrieve trial period stopping date
     *
     * @param string $profileId
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTrialStopDate($profileId) {
        $profile = $this->getProfile($profileId);
        $profileDefinition = $profile->getProfileDefinition();
        $stopDate = $profile->getStartDate();

        if ($profileDefinition->getIsTrialPeriodEnabled()) {
            $schedule = $this->getSchedule($profileId);
            if ($schedule) {
                $filter = $this->createFilter('payment_period', ['eq' => PaymentInterface::PERIOD_TRIAL]);

                $trialCyclesRest = $schedule->getTrialTotalCount() - $schedule->getTrialCount();
                if ($trialCyclesRest > 0) {
                    $lastScheduledPaymentDate = $this->getLastScheduledPaymentDate($profileId, $filter);
                    if ($lastScheduledPaymentDate) {
                        $stopDate = $lastScheduledPaymentDate;
                    }
                    $stopDate = $this->shiftDate(
                        $stopDate,
                        $trialCyclesRest,
                        $profileDefinition->getTrialBillingPeriod(),
                        $profileDefinition->getTrialBillingFrequency(),
                        false
                    );
                } else {
                    $lastPaid = $this->paymentsList->getLastPaid($profileId, $filter);
                    if ($lastPaid->getId()) {
                        $stopDate = $lastPaid->getPaidAt();
                    } else {
                        $stopDate = $this->shiftDate(
                            $stopDate,
                            $profileDefinition->getTrialTotalBillingCycles(),
                            $profileDefinition->getTrialBillingPeriod(),
                            $profileDefinition->getTrialBillingFrequency(),
                            false
                        );
                    }
                }
            }
        }

        return $stopDate;
    }

    /**
     * Retrieve regular period starting date
     *
     * @param int $profileId
     * @param bool $skipInitialPayment
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRegularStartDate($profileId, $skipInitialPayment = false) {
        $profile = $this->getProfile($profileId);
        $profileDefinition = $profile->getProfileDefinition();
        $isTrial = $profileDefinition->getIsTrialPeriodEnabled();
        $isInitial = $profileDefinition->getIsInitialFeeEnabled() && $skipInitialPayment;
        $startDate = $profile->getStartDate();

        if ($isTrial || $isInitial) {
            // initial date setting
            if ($isTrial) {
                $startDate = $this->shiftDate(
                    $this->getTrialStopDate($profileId),
                    1,
                    $profileDefinition->getTrialBillingPeriod(),
                    $profileDefinition->getTrialBillingFrequency()
                );
            } elseif ($isInitial) {
                $startDate = $this->shiftDate(
                    $startDate,
                    1,
                    $profileDefinition->getBillingPeriod(),
                    $profileDefinition->getBillingFrequency()
                );
            }

            // payment date adjustment
            $schedule = $this->getSchedule($profileId);
            if ($schedule) {
                $filter = $this->createFilter('payment_period', ['eq' => PaymentInterface::PERIOD_REGULAR]);
                if ($schedule->getRegularCount() == 0 && $isTrial) {
                    // retrieve date from scheduled regular payment
                    $lastScheduledPaymentDate = $this->getLastScheduledPaymentDate($profileId, $filter);
                    if ($lastScheduledPaymentDate) {
                        $startDate = $lastScheduledPaymentDate;
                    }
                } elseif ($schedule->getRegularCount() == 1 && $isInitial && !$isTrial) {
                    // retrieve date from scheduled regular payment
                    $lastScheduledPaymentDate = $this->getLastScheduledPaymentDate($profileId, $filter);
                    if ($lastScheduledPaymentDate) {
                        $startDate = $lastScheduledPaymentDate;
                    }
                } else {
                    // retrieve date from 2nd paid regular payment
                    $secondPaid = $this->paymentsList->getFirstPaid($profileId, $filter);
                    if ($secondPaid->getId()) {
                        $startDate = $secondPaid->getPaidAt();
                    }
                }
            }
        }

        return $startDate;
    }

    /**
     * Retrieve regular period stopping date
     *
     * @param int $profileId
     * @param bool $includeMembership
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRegularStopDate($profileId, $includeMembership = true) {
        $profile = $this->getProfile($profileId);
        $profileDefinition = $profile->getProfileDefinition();
        $stopDate = $this->getRegularStartDate($profileId);

        $schedule = $this->getSchedule($profileId);
        if ($schedule) {
            $filter = $this->createFilter('payment_period', ['eq' => PaymentInterface::PERIOD_REGULAR]);

            $regularCyclesRest = $schedule->getRegularTotalCount() - $schedule->getRegularCount();
            if ($regularCyclesRest > 0) {
                $lastScheduledPaymentDate = $this->getLastScheduledPaymentDate($profileId, $filter);
                if ($lastScheduledPaymentDate) {
                    $stopDate = $lastScheduledPaymentDate;
                }
                $stopDate = $this->shiftDate(
                    $stopDate,
                    $regularCyclesRest,
                    $profileDefinition->getBillingPeriod(),
                    $profileDefinition->getBillingFrequency(),
                    false
                );
            } else {
                $stopDate = $profile->getLastOrderDate();
            }
        }

        if ($profileDefinition->getIsMembershipModelEnabled() && $includeMembership) {
            $stopDate = $this->shiftDate(
                $stopDate,
                1,
                $profileDefinition->getBillingPeriod(),
                $profileDefinition->getBillingFrequency()
            );
        }

        return $stopDate;
    }

    /**
     * Shift date on X billing period by profile definition
     *
     * @param string $date
     * @param int $cyclesCount
     * @param int $period
     * @param string $frequency
     * @param bool $inclusiveLastPeriod
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
     * Retrieve profile by profle id
     *
     * @param $profileId
     * @return \Aheadworks\Sarp2\Api\Data\ProfileInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getProfile($profileId)
    {
        return $this->profileRepository->get($profileId);
    }

    /**
     * Create filter data object for collection
     *
     * @param string $field
     * @param $condition
     * @return \Magento\Framework\DataObject
     */
    private function createFilter($field, $condition = null)
    {
        return $this->dataObjectFactory->create([
            'field' => $field,
            'condition' => $condition
        ]);
    }

    /**
     * Retrieve schedule by profile id
     *
     * @param $profileId
     * @return \Aheadworks\Sarp2\Engine\Payment\Schedule|null
     */
    private function getSchedule($profileId)
    {
        $schedule = null;
        try {
            $schedule = $this->schedulePersistence->getByProfile($profileId);
        } catch (NoSuchEntityException $exception) {
        }

        return $schedule;
    }

    /**
     * Retrieve first scheduled payment date
     *
     * @param int $profileId
     * @param \Magento\Framework\DataObject $filter
     * @return string|null
     */
    private function getLastScheduledPaymentDate($profileId, $filter)
    {
        $lastScheduled = $this->paymentsList->getFirstPaid($profileId, $filter);
        foreach ($lastScheduled as $payment) {
            return $payment->getType() == PaymentInterface::TYPE_REATTEMPT
                ? $payment->getRetryAt()
                : $payment->getScheduledAt();
        }

        return null;
    }

    /**
     * Retrieve last scheduled payment date
     *
     * @param int $profileId
     * @param \Magento\Framework\DataObject $filter
     * @return string|null
     */
    private function getFirstScheduledOrPaidPaymentDate($profileId, $filter)
    {
        $firstScheduledOrPaid = $this->paymentsList->getFirstScheduledOrPaid($profileId, $filter);
        foreach ($firstScheduledOrPaid as $payment) {
            return $payment->getType() == PaymentInterface::TYPE_REATTEMPT
                ? $payment->getRetryAt()
                : $payment->getScheduledAt();
        }

        return null;
    }
}
