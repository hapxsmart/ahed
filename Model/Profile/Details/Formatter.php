<?php
namespace Aheadworks\Sarp2\Model\Profile\Details;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Plan\Period\Formatter as PeriodFormatter;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Magento\Framework\Phrase;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Formatter
 *
 * @package Aheadworks\Sarp2\Model\Profile\Details
 */
class Formatter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var PeriodFormatter
     */
    private $periodFormatter;

    /**
     * @var PriceCurrency
     */
    private $priceCurrencyFormatter;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param Config $config
     * @param PeriodFormatter $periodFormatter
     * @param PriceCurrency $priceCurrencyFormatter
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        Config $config,
        PeriodFormatter $periodFormatter,
        PriceCurrency $priceCurrencyFormatter,
        TimezoneInterface $localeDate
    ) {
        $this->config = $config;
        $this->periodFormatter = $periodFormatter;
        $this->priceCurrencyFormatter = $priceCurrencyFormatter;
        $this->localeDate = $localeDate;
    }

    /**
     * Check if show initial payment details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return bool
     */
    public function isShowInitialDetails($planDefinition)
    {
        return (bool)$planDefinition->getIsInitialFeeEnabled();
    }

    /**
     * Check if show trial period details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $forChangePlan
     * @return bool
     */
    public function isShowTrialDetails($planDefinition, $forChangePlan = false)
    {
        $isShow =
            ($planDefinition->getIsTrialPeriodEnabled() && !$planDefinition->getIsInitialFeeEnabled())
            || ($planDefinition->getIsTrialPeriodEnabled()
                && $planDefinition->getIsInitialFeeEnabled()
                && $planDefinition->getTrialTotalBillingCycles() > 1
            )
            || ($planDefinition->getIsTrialPeriodEnabled()
                && $forChangePlan
            );

        return (bool)$isShow;
    }

    /**
     * Check if show regular period details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return bool
     */
    public function isShowRegularDetails($planDefinition)
    {
        $isShow =
            !($planDefinition->getIsInitialFeeEnabled()
                && $planDefinition->getTotalBillingCycles() == 1
                && !$planDefinition->getIsTrialPeriodEnabled()
            );
        return (bool)$isShow;
    }

    /**
     * Retrieve first payment label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getInitialPaymentLabel()
    {
        return __('First payment');
    }

    /**
     * Retrieve trial offer label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param float $trialPrice
     * @param bool $skipInitialFee
     * @return \Magento\Framework\Phrase
     */
    public function getTrialOfferLabel($planDefinition, $trialPrice, $skipInitialFee = true)
    {
        $trialCycles = $planDefinition->getTrialTotalBillingCycles();
        if ($planDefinition->getIsInitialFeeEnabled() && $skipInitialFee) {
            $trialCycles--;
        }

        if ($trialCycles > 1 && $trialPrice > 0) {
            $period = $this->periodFormatter->formatPeriodicity(
                $planDefinition->getTrialBillingPeriod(),
                $planDefinition->getTrialBillingFrequency()
            );

            return $this->config->isAlternativeSubscriptionDetailsView()
                ? __('Trial Payments')
                : __('Trial Payments / %1', [$period]);
        } else {
            return __('Trial Offer');
        }
    }

    /**
     * Retrieve regular offer label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $skipInitialFee
     * @return \Magento\Framework\Phrase
     */
    public function getRegularOfferLabel($planDefinition, $skipInitialFee = true)
    {
        $regularCycles = $planDefinition->getTotalBillingCycles();
        if ($regularCycles > 0
            && $planDefinition->getIsInitialFeeEnabled()
            && !$planDefinition->getIsTrialPeriodEnabled()
            && $skipInitialFee
        ) {
            $regularCycles--;
        }

        if ($regularCycles == 1) {
            return __('Regular Offer');
        } else {
            $period = $this->periodFormatter->formatPeriodicity(
                $planDefinition->getBillingPeriod(),
                $planDefinition->getBillingFrequency()
            );

            return $this->config->isAlternativeSubscriptionDetailsView()
                ? __('Regular Payments')
                : __('Regular Payments / %1', [$period]);
        }
    }

    /**
     * Retrieve first payment
     *
     * @param float $fee
     * @param float $firstPaymentAmount
     * @param string|null $currencyCode
     * @return \Magento\Framework\Phrase
     */
    public function getInitialPaymentPrice($fee, $firstPaymentAmount, $currencyCode = null)
    {
        $sum = $firstPaymentAmount + $fee;
        $formattedFee = $this->formatPrice($fee, $currencyCode);
        $formattedSum = $this->formatPrice($sum, $currencyCode);

        return __('%1 (inc. %2 initial fee)', [$formattedSum, $formattedFee]);
    }

    /**
     * Retrieve trial price and cycles
     *
     * @param float $trialPrice
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $skipInitialFee
     * @param bool $withoutPrice
     * @param string|null $currencyCode
     * @return \Magento\Framework\Phrase
     */
    public function getTrialPriceAndCycles(
        $trialPrice,
        $planDefinition,
        $skipInitialFee = true,
        $withoutPrice = false,
        $currencyCode = null
    ) {
        $trialCycles = $planDefinition->getTrialTotalBillingCycles();
        if ($planDefinition->getIsInitialFeeEnabled() && $skipInitialFee) {
            $trialCycles--;
        }

        $formattedTrialPrice = '';
        if (!$withoutPrice) {
            $formattedTrialPrice = $trialPrice == 0
                ? __('Free')
                : $this->formatPrice($trialPrice, $currencyCode);
        }

        if ($this->config->isAlternativeSubscriptionDetailsView()) {
            $period = $this->periodFormatter->formatPeriod(
                $planDefinition->getTrialBillingPeriod(),
                $planDefinition->getTrialBillingFrequency()
            );
            $totalCycles = $this->periodFormatter->formatTotalCycles(
                $planDefinition->getTrialBillingPeriod(),
                $planDefinition->getTrialBillingFrequency(),
                $trialCycles,
                true
            );
            return $trialCycles > 1 && $trialPrice > 0
                ? __('%1 / %2 for %3', [$formattedTrialPrice, $period, $totalCycles])
                : __('%1 for %2', [$formattedTrialPrice, $totalCycles]);
        } else {
            return $trialCycles > 1 && $trialPrice > 0
                ? __('%1 x %2', [$trialCycles, $formattedTrialPrice])
                : __('%1', [$formattedTrialPrice]);
        }
    }

    /**
     * Retrieve trial price and cycles
     *
     * @param float $regularPrice
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $skipInitialFee
     * @param bool $withoutPrice
     * @param string|null $currencyCode
     * @return \Magento\Framework\Phrase
     */
    public function getRegularPriceAndCycles(
        $regularPrice,
        $planDefinition,
        $skipInitialFee = true,
        $withoutPrice = false,
		$currencyCode = null
    ) {
        $regularCycles = $planDefinition->getTotalBillingCycles();
        if ($regularCycles > 0
            && $planDefinition->getIsInitialFeeEnabled()
            && !$planDefinition->getIsTrialPeriodEnabled()
            && $skipInitialFee
        ) {
            $regularCycles--;
        }

        $formattedRegularPrice = $this->formatPrice($regularPrice, $currencyCode);
        if ($withoutPrice) {
            $formattedRegularPrice = '';
        }

        if ($this->config->isAlternativeSubscriptionDetailsView()) {
            $period = $this->getFormattedPeriod($planDefinition);
            $totalCycles = $this->periodFormatter->formatTotalCycles(
                $planDefinition->getBillingPeriod(),
                $planDefinition->getBillingFrequency(),
                $regularCycles,
                true
            );

            if ($regularCycles > 1) {
                return __('%1 / %2 for %3', [$formattedRegularPrice, $period, $totalCycles]);
            } elseif($regularCycles == 1) {
                return __('%1 for %2', [$formattedRegularPrice, $totalCycles]);
            } else {
                return __('%1 / %2', [$formattedRegularPrice, $period]);
            }
        } else {
            return $regularCycles > 1
                ? __('%1 x %2', [$regularCycles, $formattedRegularPrice])
                : __('%1', [$formattedRegularPrice]);
        }
    }

    /**
     * Retrieve formatted period phrase
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return string
     */
    public function getFormattedPeriod($planDefinition) {
        return $this->periodFormatter->formatPeriod(
            $planDefinition->getBillingPeriod(),
            $planDefinition->getBillingFrequency()
        );
    }

    /**
     * Retrieve subscription ends label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
     */
    public function getSubscriptionEndsDateLabel($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Last Payment')
            : __('Subscription Ends');
    }

    /**
     * Retrieve subscription created on label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return Phrase
    */
    public function getSubscriptionCreatedOnLabel($planDefinition)
    {
        return $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
            ? __('Installment Created On')
            : __('Subscription Created On');
    }

    /**
     * Retrieve subscription stop date
     *
     * @param string $subscriptionStopDate
     * @param PlanDefinitionInterface $profileDefinition
     * @return string
     */
    public function formatRegularStopDate($subscriptionStopDate, $profileDefinition)
    {
        return $profileDefinition->getTotalBillingCycles() > 0
            ? $this->formatDate($subscriptionStopDate)
            : __('Cancel Anytime');
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

    /**
     * Format price
     *
     * @param float $price
     * @param string|null $currencyCode
     * @return float
     */
    private function formatPrice($price, $currencyCode)
    {
        return $this->priceCurrencyFormatter->format(
            $price,
            false,
            $this->priceCurrencyFormatter::DEFAULT_PRECISION,
            null,
            $currencyCode
        );
    }
}
