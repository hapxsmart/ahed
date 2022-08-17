<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PlanDefinitionInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PlanDefinitionInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const DEFINITION_ID = 'definition_id';
    const BILLING_PERIOD = 'billing_period';
    const BILLING_FREQUENCY = 'billing_frequency';
    const TRIAL_BILLING_PERIOD = 'trial_billing_period';
    const TRIAL_BILLING_FREQUENCY = 'trial_billing_frequency';
    const TOTAL_BILLING_CYCLES = 'total_billing_cycles';
    const START_DATE_TYPE = 'start_date_type';
    const START_DATE_DAY_OF_MONTH = 'start_date_day_of_month';
    const IS_INITIAL_FEE_ENABLED = 'is_initial_fee_enabled';
    const IS_TRIAL_PERIOD_ENABLED = 'is_trial_period_enabled';
    const TRIAL_TOTAL_BILLING_CYCLES = 'trial_total_billing_cycles';
    const IS_MEMBERSHIP_MODEL_ENABLED = 'is_membership_model_enabled';
    const IS_EXTEND_ENABLE = 'is_extend_enable';
    const OFFER_EXTEND_EMAIL_OFFSET = 'offer_extend_email_offset';
    const OFFER_EXTEND_EMAIL_TEMPLATE = 'offer_extend_email_template';
    const UPCOMING_BILLING_EMAIL_OFFSET = 'upcoming_billing_email_offset';
    const UPCOMING_TRIAL_BILLING_EMAIL_OFFSET = 'upcoming_trial_billing_email_offset';
    const IS_ALLOW_SUBSCRIPTION_CANCELLATION = 'is_allow_subscription_cancellation';
    const FRONTEND_DISPLAYING_MODE = 'frontend_displaying_mode';
    /**#@-*/

    /**
     * Get plan definition ID
     *
     * @return int|null
     */
    public function getDefinitionId();

    /**
     * Set plan definition ID
     *
     * @param int $definitionId
     * @return $this
     */
    public function setDefinitionId($definitionId);

    /**
     * Get billing period
     *
     * @return string
     */
    public function getBillingPeriod();

    /**
     * Set billing period
     *
     * @param string $billingPeriod
     * @return $this
     */
    public function setBillingPeriod($billingPeriod);

    /**
     * Get billing frequency
     *
     * @return int
     */
    public function getBillingFrequency();

    /**
     * Set billing frequency
     *
     * @param int $billingFrequency
     * @return $this
     */
    public function setBillingFrequency($billingFrequency);

    /**
     * Get trial billing period
     *
     * @return string
     */
    public function getTrialBillingPeriod();

    /**
     * Set trial billing period
     *
     * @param string $trialBillingPeriod
     * @return $this
     */
    public function setTrialBillingPeriod($trialBillingPeriod);

    /**
     * Get trial billing frequency
     *
     * @return int
     */
    public function getTrialBillingFrequency();

    /**
     * Set trial billing frequency
     *
     * @param int $trialBillingFrequency
     * @return $this
     */
    public function setTrialBillingFrequency($trialBillingFrequency);

    /**
     * Get total billing cycles
     *
     * @return int
     */
    public function getTotalBillingCycles();

    /**
     * Set total billing cycles
     *
     * @param int $totalBillingCycles
     * @return $this
     */
    public function setTotalBillingCycles($totalBillingCycles);

    /**
     * Get start date type
     *
     * @return string
     */
    public function getStartDateType();

    /**
     * Set start date type
     *
     * @param string $startDateType
     * @return $this
     */
    public function setStartDateType($startDateType);

    /**
     * Get day of month of start date
     *
     * @return int|null
     */
    public function getStartDateDayOfMonth();

    /**
     * Set day of month of start date
     *
     * @param int $startDateDayOfMonth
     * @return $this
     */
    public function setStartDateDayOfMonth($startDateDayOfMonth);

    /**
     * Get is initial fee enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsInitialFeeEnabled();

    /**
     * Set is initial fee enabled
     *
     * @param bool $isInitialFeeEnabled
     * @return $this
     */
    public function setIsInitialFeeEnabled($isInitialFeeEnabled);

    /**
     * Get is trial period enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsTrialPeriodEnabled();

    /**
     * Set is trial period enabled
     *
     * @param bool $isTrialPeriodEnabled
     * @return $this
     */
    public function setIsTrialPeriodEnabled($isTrialPeriodEnabled);

    /**
     * Get trial total billing cycles
     *
     * @return int
     */
    public function getTrialTotalBillingCycles();

    /**
     * Set trial total billing cycles
     *
     * @param int $trialTotalBillingCycles
     * @return $this
     */
    public function setTrialTotalBillingCycles($trialTotalBillingCycles);

    /**
     * Get is membership model enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsMembershipModelEnabled();

    /**
     * Set is membership model enabled
     *
     * @param bool $isMembershipModelEnabled
     * @return $this
     */
    public function setIsMembershipModelEnabled($isMembershipModelEnabled);

    /**
     * Get is offer extend option
     *
     * @return bool
     */
    public function getIsExtendEnable();

    /**
     * Set is offer extend option
     *
     * @param bool $isExtendEnable
     * @return $this
     */
    public function setIsExtendEnable($isExtendEnable);

    /**
     * Get offer extend email offset
     *
     * @return int
     */
    public function getOfferExtendEmailOffset();

    /**
     * Set offer extend email offset
     *
     * @param int $offset
     * @return $this
     */
    public function setOfferExtendEmailOffset($offset);

    /**
     * Get offer extend email template
     *
     * @return string
     */
    public function getOfferExtendEmailTemplate();

    /**
     * Set offer extend email template
     *
     * @param string $template
     * @return $this
     */
    public function setOfferExtendEmailTemplate($template);

    /**
     * Get upcoming billing email offset
     *
     * @return int
     */
    public function getUpcomingBillingEmailOffset();

    /**
     * Set upcoming billing email offset
     *
     * @param int $upcomingBillingEmailOffset
     * @return $this
     */
    public function setUpcomingBillingEmailOffset($upcomingBillingEmailOffset);

    /**
     * Get upcoming trial billing email offset
     *
     * @return int
     */
    public function getUpcomingTrialBillingEmailOffset();

    /**
     * Set upcoming trial billing email offset
     *
     * @param int $upcomingTrialBillingEmailOffset
     * @return $this
     */
    public function setUpcomingTrialBillingEmailOffset($upcomingTrialBillingEmailOffset);

    /**
     * Get is allow cancellation of subscription option
     *
     * @return int
     */
    public function getIsAllowSubscriptionCancellation();

    /**
     * Set is allow cancellation of subscription option
     *
     * @param bool $isAllowSubscriptionCancellation
     * @return $this
     */
    public function setIsAllowSubscriptionCancellation($isAllowSubscriptionCancellation);

    /**
     * Get frontend displaying mode
     *
     * @return string
     */
    public function getFrontendDisplayingMode();

    /**
     * Set frontend displaying mode
     *
     * @param string $frontendDisplayingMode
     * @return $this
     */
    public function setFrontendDisplayingMode($frontendDisplayingMode);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\PlanDefinitionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\PlanDefinitionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\PlanDefinitionExtensionInterface $extensionAttributes
    );
}
