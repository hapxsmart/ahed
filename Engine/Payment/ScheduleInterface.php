<?php
namespace Aheadworks\Sarp2\Engine\Payment;

/**
 * Interface ScheduleInterface
 * @package Aheadworks\Sarp2\Engine\Payment
 */
interface ScheduleInterface
{
    const PROFILE_ID = 'profile_id';
    const REGULAR_COUNT = 'regular_count';
    const REGULAR_TOTAL_COUNT = 'regular_total_count';
    const IS_MEMBERSHIP_MODEL = 'is_membership_model';
    const MEMBERSHIP_COUNT = 'membership_count';
    const MEMBERSHIP_TOTAL_COUNT = 'membership_total_count';
    const TRIAL_PERIOD = 'trial_period';
    const TRIAL_FREQUENCY = 'trial_frequency';

    /**
     * Get schedule Id
     *
     * @return int|null
     */
    public function getScheduleId();

    /**
     * Set schedule Id
     *
     * @param int $profileId
     * @return $this
     */
    public function setScheduleId($profileId);

    /**
     * Get profile Id
     *
     * @return int|null
     */
    public function getProfileId();

    /**
     * Set profile Id
     *
     * @param int $scheduleId
     * @return $this
     */
    public function setProfileId($scheduleId);

    /**
     * Get payments period
     *
     * @return string
     */
    public function getPeriod();

    /**
     * Set payments period
     *
     * @param string $period
     * @return $this
     */
    public function setPeriod($period);

    /**
     * Get payments frequency
     *
     * @return int
     */
    public function getFrequency();

    /**
     * Get payments frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency);

    /**
     * Get trial payments period
     *
     * @return string
     */
    public function getTrialPeriod();

    /**
     * Set trial payments period
     *
     * @param string $period
     * @return $this
     */
    public function setTrialPeriod($period);

    /**
     * Get trial payments frequency
     *
     * @return int
     */
    public function getTrialFrequency();

    /**
     * Get trial payments frequency
     *
     * @param int $frequency
     * @return $this
     */
    public function setTrialFrequency($frequency);

    /**
     * Check if initial fee paid
     *
     * @return bool
     */
    public function isInitialPaid();

    /**
     * Set initial fee paid flag
     *
     * @param bool $isInitialPaid
     * @return $this
     */
    public function setIsInitialPaid($isInitialPaid);

    /**
     * Get trial payments count
     *
     * @return int
     */
    public function getTrialCount();

    /**
     * Set trial payments count
     *
     * @param int $trialCount
     * @return $this
     */
    public function setTrialCount($trialCount);

    /**
     * Get trial payments total count
     *
     * @return int
     */
    public function getTrialTotalCount();

    /**
     * Set trial payments total count
     *
     * @param int $trialTotalCount
     * @return $this
     */
    public function setTrialTotalCount($trialTotalCount);

    /**
     * Get regular payments count
     *
     * @return int
     */
    public function getRegularCount();

    /**
     * Set regular payments count
     *
     * @param int $regularCount
     * @return $this
     */
    public function setRegularCount($regularCount);

    /**
     * Get regular payments total count
     *
     * @return int
     */
    public function getRegularTotalCount();

    /**
     * Set regular payments total count
     *
     * @param int $regularTotalCount
     * @return $this
     */
    public function setRegularTotalCount($regularTotalCount);

    /**
     * Check if schedule is reactivated
     *
     * @return bool
     */
    public function isReactivated();

    /**
     * Set reactivated flag
     *
     * @param bool $isReactivated
     * @return $this
     */
    public function setIsReactivated($isReactivated);

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get is membership model
     *
     * @return bool
     */
    public function isMembershipModel();

    /**
     * Set is membership model
     *
     * @param bool $isMembershipModel
     * @return $this
     */
    public function setIsMembershipModel($isMembershipModel);

    /**
     * Get membership payments count
     *
     * @return int
     */
    public function getMembershipCount();

    /**
     * Set membership payments count
     *
     * @param int $membershipCount
     * @return $this
     */
    public function setMembershipCount($membershipCount);

    /**
     * Get membership payments total count
     *
     * @return int
     */
    public function getMembershipTotalCount();

    /**
     * Set membership payments total count
     *
     * @param int $membershipTotalCount
     * @return $this
     */
    public function setMembershipTotalCount($membershipTotalCount);
}
