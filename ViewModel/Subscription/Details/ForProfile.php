<?php
namespace Aheadworks\Sarp2\ViewModel\Subscription\Details;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Aheadworks\Sarp2\Model\Profile\DateResolver as ProfileDateResolver;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter as DetailsFormatter;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ForProfile
 *
 * @package Aheadworks\Sarp2\ViewModel\Subscription\Details
 */
class ForProfile implements ArgumentInterface
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var DetailsFormatter
     */
    private $detailsFormatter;

    /**
     * @var ProfileDateResolver
     */
    private $profileDateResolver;

    /**
     * @param DetailsFormatter $detailsFormatter
     * @param TimezoneInterface $localeDate
     * @param ProfileDateResolver $profileDateResolver
     */
    public function __construct(
        DetailsFormatter $detailsFormatter,
        TimezoneInterface $localeDate,
        ProfileDateResolver $profileDateResolver
    ) {
        $this->detailsFormatter = $detailsFormatter;
        $this->localeDate = $localeDate;
        $this->profileDateResolver = $profileDateResolver;
    }

    /**
     * Retrieve subscription start date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCreatedDate($profile)
    {
        return $this->formatDate(
            $this->profileDateResolver->getStartDate($profile->getProfileId())
        );
    }

    /**
     * Check if show initial payment details
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isShowInitialDetails($profile)
    {
        return $this->detailsFormatter->isShowInitialDetails(
            $profile->getProfileDefinition()
        );
    }

    /**
     * Check if show trial period details
     *
     * @param ProfileInterface $profile
     * @param bool $forChangePlan
     * @return bool
     */
    public function isShowTrialDetails($profile, $forChangePlan = false)
    {
        return $this->detailsFormatter->isShowTrialDetails(
            $profile->getProfileDefinition(),
            $forChangePlan
        );
    }

    /**
     * Check if show regular period details
     *
     * @param ProfileInterface $profile
     * @return bool
     */
    public function isShowRegularDetails($profile)
    {
        return $this->detailsFormatter->isShowRegularDetails(
            $profile->getProfileDefinition()
        );
    }

    /**
     * Retrieve trial label
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getInitialLabel($profile)
    {
        return $this->detailsFormatter->getInitialPaymentLabel();
    }

    /**
     * Retrieve trial label
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getTrialLabel($profile)
    {
        return $this->detailsFormatter->getTrialOfferLabel(
            $profile->getProfileDefinition(),
            $profile->getTrialGrandTotal()
        );
    }

    /**
     * Retrieve regular label
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getRegularLabel($profile)
    {
        return $this->detailsFormatter->getRegularOfferLabel($profile->getProfileDefinition());
    }

    /**
     * Retrieve first payment price
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getInitialPaymentPrice($profile)
    {
        $fee = $profile->getInitialFee();
        $firstPaymentAmount = $profile->getProfileDefinition()->getIsTrialPeriodEnabled()
            ? $profile->getTrialSubtotal()
            : $profile->getRegularSubtotal();
        $currencyCode = $profile->getProfileCurrencyCode();

        return $this->detailsFormatter->getInitialPaymentPrice($fee, $firstPaymentAmount, $currencyCode);
    }

    /**
     * Retrieve first payment start date
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getInitialStartDate($profile)
    {
        return $this->formatDate(
            $this->profileDateResolver->getInitialStartDate($profile->getProfileId())
        );
    }

    /**
     * Retrieve trial price
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getTrialPriceAndCycles($profile)
    {
        return $this->detailsFormatter->getTrialPriceAndCycles(
            $profile->getTrialSubtotal(),
            $profile->getProfileDefinition(),
            true,
            false,
            $profile->getProfileCurrencyCode()
        );
    }

    /**
     * Retrieve regular price
     *
     * @param ProfileInterface $profile
     * @return string
     */
    public function getRegularPriceAndCycles($profile)
    {
        return $this->detailsFormatter->getRegularPriceAndCycles(
            $profile->getRegularSubtotal(),
            $profile->getProfileDefinition(),
            true,
            false,
            $profile->getProfileCurrencyCode()
        );
    }

    /**
     * Retrieve trial start date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTrialStartDate($profile)
    {
        return $this->formatDate(
            $this->profileDateResolver->getTrialStartDate($profile->getProfileId(), true)
        );
    }

    /**
     * Retrieve trial stop date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTrialStopDate($profile)
    {
        return $this->formatDate(
            $this->profileDateResolver->getTrialStopDate($profile->getProfileId())
        );
    }

    /**
     * Retrieve regular start date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRegularStartDate($profile)
    {
        return $this->formatDate(
            $this->profileDateResolver->getRegularStartDate(
                $profile->getProfileId(),
                true
            )
        );
    }

    /**
     * Retrieve subscription ends label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getSubscriptionEndLabel($planDefinition)
    {
        return $this->detailsFormatter->getSubscriptionEndsDateLabel($planDefinition);
    }

    /**
     * Retrieve subscription created on label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getSubscriptionCreatedOnLabel($planDefinition)
    {
        return $this->detailsFormatter->getSubscriptionCreatedOnLabel($planDefinition);
    }

    /**
     * Retrieve subscription plan label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getSubscriptionPlanLabel($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Payment Schedule')
            : __('Payment Schedule');
    }

    /**
     * Retrieve remove item message
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getRemoveItemMessage($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Are you sure you want to remove product from this installment?')
            : __('Are you sure you want to remove product from this subscription?');
    }

    /**
     * Retrieve cancel subscription label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getCancelSubscriptionLabel($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Cancel Installment')
            : __('Cancel Subscription');
    }

    /**
     * Retrieve extend subscription label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getExtendSubscriptionLabel($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
        ? __('Extend Installment')
        : __('Extend Subscription');
    }

    /**
     * Retrieve cancel subscription message
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getCancelSubscriptionMessage($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Are you sure you want to cancel this installment?')
            : __('Are you sure you want to cancel this subscription?');
    }

    /**
     * Retrieve extend subscription message
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getExtendSubscriptionMessage($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Are you sure you want to extend this installment?')
            : __('Are you sure you want to extend this subscription?');
    }

    /**
     * Retrieve regular stop date
     *
     * @param ProfileInterface $profile
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegularStopDate($profile)
    {
        return $this->detailsFormatter->formatRegularStopDate(
            $this->profileDateResolver->getRegularStopDate(
                $profile->getProfileId(),
                true
            ),
            $profile->getProfileDefinition()
        );
    }

    /**
     * Format date
     *
     * @param string $date
     * @return string
     */
    private function formatDate($date)
    {
        return $this->localeDate->formatDate($date, \IntlDateFormatter::MEDIUM);
    }
}
