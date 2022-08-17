<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanDefinitionExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Definition
 * @package Aheadworks\Sarp2\Model\Plan
 */
class Definition extends AbstractExtensibleObject implements PlanDefinitionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefinitionId()
    {
        return $this->_get(self::DEFINITION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinitionId($definitionId)
    {
        return $this->setData(self::DEFINITION_ID, $definitionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingPeriod()
    {
        return $this->_get(self::BILLING_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingPeriod($billingPeriod)
    {
        return $this->setData(self::BILLING_PERIOD, $billingPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getBillingFrequency()
    {
        return $this->_get(self::BILLING_FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setBillingFrequency($billingFrequency)
    {
        return $this->setData(self::BILLING_FREQUENCY, $billingFrequency);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialBillingPeriod()
    {
        return $this->_get(self::TRIAL_BILLING_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialBillingPeriod($trialBillingPeriod)
    {
        return $this->setData(self::TRIAL_BILLING_PERIOD, $trialBillingPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialBillingFrequency()
    {
        return $this->_get(self::TRIAL_BILLING_FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialBillingFrequency($trialBillingFrequency)
    {
        return $this->setData(self::TRIAL_BILLING_FREQUENCY, $trialBillingFrequency);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalBillingCycles()
    {
        return $this->_get(self::TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalBillingCycles($totalBillingCycles)
    {
        return $this->setData(self::TOTAL_BILLING_CYCLES, $totalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDateType()
    {
        return $this->_get(self::START_DATE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDateType($startDateType)
    {
        return $this->setData(self::START_DATE_TYPE, $startDateType);
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDateDayOfMonth()
    {
        return $this->_get(self::START_DATE_DAY_OF_MONTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setStartDateDayOfMonth($startDateDayOfMonth)
    {
        return $this->setData(self::START_DATE_DAY_OF_MONTH, $startDateDayOfMonth);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsInitialFeeEnabled()
    {
        return $this->_get(self::IS_INITIAL_FEE_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialFeeEnabled($isInitialFeeEnabled)
    {
        return $this->setData(self::IS_INITIAL_FEE_ENABLED, $isInitialFeeEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsTrialPeriodEnabled()
    {
        return $this->_get(self::IS_TRIAL_PERIOD_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsTrialPeriodEnabled($isTrialPeriodEnabled)
    {
        return $this->setData(self::IS_TRIAL_PERIOD_ENABLED, $isTrialPeriodEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTotalBillingCycles()
    {
        return $this->_get(self::TRIAL_TOTAL_BILLING_CYCLES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTotalBillingCycles($trialTotalBillingCycles)
    {
        return $this->setData(self::TRIAL_TOTAL_BILLING_CYCLES, $trialTotalBillingCycles);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsMembershipModelEnabled()
    {
        return $this->_get(self::IS_MEMBERSHIP_MODEL_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsMembershipModelEnabled($isMembershipModelEnabled)
    {
        return $this->setData(self::IS_MEMBERSHIP_MODEL_ENABLED, $isMembershipModelEnabled);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsExtendEnable()
    {
        return $this->_get(self::IS_EXTEND_ENABLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsExtendEnable($isExtendEnable)
    {
        return $this->setData(self::IS_EXTEND_ENABLE, $isExtendEnable);
    }

    /**
     * {@inheritdoc}
     */
    public function getOfferExtendEmailOffset()
    {
        return $this->_get(self::OFFER_EXTEND_EMAIL_OFFSET);
    }

    /**
     * {@inheritdoc}
     */
    public function setOfferExtendEmailOffset($offset)
    {
        return $this->setData(self::OFFER_EXTEND_EMAIL_OFFSET, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getOfferExtendEmailTemplate()
    {
        return $this->_get(self::OFFER_EXTEND_EMAIL_TEMPLATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOfferExtendEmailTemplate($template)
    {
        return $this->setData(self::OFFER_EXTEND_EMAIL_TEMPLATE, $template);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpcomingBillingEmailOffset()
    {
        return $this->_get(self::UPCOMING_BILLING_EMAIL_OFFSET);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpcomingBillingEmailOffset($upcomingBillingEmailOffset)
    {
        return $this->setData(self::UPCOMING_BILLING_EMAIL_OFFSET, $upcomingBillingEmailOffset);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpcomingTrialBillingEmailOffset()
    {
        return $this->_get(self::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpcomingTrialBillingEmailOffset($upcomingTrialBillingEmailOffset)
    {
        return $this->setData(self::UPCOMING_TRIAL_BILLING_EMAIL_OFFSET, $upcomingTrialBillingEmailOffset);
    }

    /**
     * @inheritDoc
     */
    public function getIsAllowSubscriptionCancellation()
    {
        return $this->_get(self::IS_ALLOW_SUBSCRIPTION_CANCELLATION);
    }

    /**
     * @inheritDoc
     */
    public function setIsAllowSubscriptionCancellation($isAllowSubscriptionCancellation)
    {
        return $this->setData(self::IS_ALLOW_SUBSCRIPTION_CANCELLATION, $isAllowSubscriptionCancellation);
    }

    /**
     * @inheritDoc
     */
    public function getFrontendDisplayingMode()
    {
        return $this->_get(self::FRONTEND_DISPLAYING_MODE);
    }

    /**
     * @inheritDoc
     */
    public function setFrontendDisplayingMode($frontendDisplayingMode)
    {
        return $this->setData(self::FRONTEND_DISPLAYING_MODE, $frontendDisplayingMode);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(PlanDefinitionExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
