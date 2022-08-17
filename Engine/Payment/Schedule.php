<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Schedule as ScheduleResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Schedule
 * @package Aheadworks\Sarp2\Engine\Payment
 */
class Schedule extends AbstractModel implements ScheduleInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ScheduleResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduleId()
    {
        return $this->getData('schedule_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setScheduleId($scheduleId)
    {
        return $this->setData('schedule_id', $scheduleId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPeriod()
    {
        return $this->getData('period');
    }

    /**
     * {@inheritdoc}
     */
    public function setPeriod($period)
    {
        return $this->setData('period', $period);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrequency()
    {
        return $this->getData('frequency');
    }

    /**
     * {@inheritdoc}
     */
    public function setFrequency($frequency)
    {
        return $this->setData('frequency', $frequency);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPeriod()
    {
        return $this->getData(self::TRIAL_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPeriod($period)
    {
        return $this->setData(self::TRIAL_PERIOD, $period);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialFrequency()
    {
        return $this->getData(self::TRIAL_FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialFrequency($frequency)
    {
        return $this->setData(self::TRIAL_FREQUENCY, $frequency);
    }

    /**
     * {@inheritdoc}
     */
    public function isInitialPaid()
    {
        return (bool) $this->getData('is_initial_paid');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialPaid($isInitialPaid)
    {
        return $this->setData('is_initial_paid', $isInitialPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialCount()
    {
        return $this->getData('trial_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialCount($trialCount)
    {
        return $this->setData('trial_count', $trialCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTotalCount()
    {
        return $this->getData('trial_total_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTotalCount($trialTotalCount)
    {
        return $this->setData('trial_total_count', $trialTotalCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularCount()
    {
        return $this->getData(self::REGULAR_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularCount($regularCount)
    {
        return $this->setData(self::REGULAR_COUNT, $regularCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularTotalCount()
    {
        return $this->getData(self::REGULAR_TOTAL_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularTotalCount($regularTotalCount)
    {
        return $this->setData(self::REGULAR_TOTAL_COUNT, $regularTotalCount);
    }

    /**
     * {@inheritdoc}
     */
    public function isReactivated()
    {
        return (bool) $this->getData('is_reactivated');
    }

    /**
     * {@inheritdoc}
     */
    public function setIsReactivated($isReactivated)
    {
        return $this->setData('is_reactivated', $isReactivated);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function isMembershipModel()
    {
        return $this->getData(self::IS_MEMBERSHIP_MODEL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsMembershipModel($isMembershipModel)
    {
        return $this->setData('is_membership_model', $isMembershipModel);
    }

    /**
     * {@inheritdoc}
     */
    public function getMembershipCount()
    {
        return $this->getData(self::MEMBERSHIP_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMembershipCount($membershipCount)
    {
        return $this->setData(self::MEMBERSHIP_COUNT, $membershipCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getMembershipTotalCount()
    {
        return $this->getData(self::MEMBERSHIP_TOTAL_COUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setMembershipTotalCount($membershipTotalCount)
    {
        return $this->setData(self::MEMBERSHIP_TOTAL_COUNT, $membershipTotalCount);
    }
}
