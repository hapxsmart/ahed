<?php
namespace Aheadworks\Sarp2\ViewModel\Subscription\Details;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface as ProfileItem;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\DateResolver as ProfileDateResolver;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter as DetailsFormatter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ForProfileItem
 *
 * @package Aheadworks\Sarp2\ViewModel\Subscription\Details
 */
class ForProfileItem implements ArgumentInterface
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
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param DetailsFormatter $detailsFormatter
     * @param TimezoneInterface $localeDate
     * @param ProfileDateResolver $profileDateResolver
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        DetailsFormatter $detailsFormatter,
        TimezoneInterface $localeDate,
        ProfileDateResolver $profileDateResolver,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->detailsFormatter = $detailsFormatter;
        $this->localeDate = $localeDate;
        $this->profileDateResolver = $profileDateResolver;
        $this->profileRepository = $profileRepository;
    }

    /**
     * Check if show initial payment details
     *
     * @param ProfileItem $item
     * @return bool
     * @throws LocalizedException
     */
    public function isShowInitialDetails($item)
    {
        return $this->detailsFormatter->isShowInitialDetails(
            $this->getProfileDefinition($item)
        );
    }

    /**
     * Check if show trial period details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return bool
     */
    public function isShowTrialDetails(PlanDefinitionInterface $planDefinition)
    {
        return $this->detailsFormatter->isShowTrialDetails($planDefinition);
    }

    /**
     * Check if show regular period details
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return bool
     */
    public function isShowRegularDetails(PlanDefinitionInterface $planDefinition)
    {
        return $this->detailsFormatter->isShowRegularDetails($planDefinition);
    }

    /**
     * Retrieve trial label
     *
     * @return string
     */
    public function getInitialLabel()
    {
        return $this->detailsFormatter->getInitialPaymentLabel();
    }

    /**
     * Retrieve trial label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param array $options
     * @return string
     */
    public function getTrialLabel(PlanDefinitionInterface $planDefinition, array $options)
    {
        return $this->detailsFormatter->getTrialOfferLabel(
            $planDefinition,
            $options['aw_sarp2_subscription_option']['trial_price']
        );
    }

    /**
     * Retrieve regular label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return string
     */
    public function getRegularLabel(PlanDefinitionInterface $planDefinition)
    {
        return $this->detailsFormatter->getRegularOfferLabel($planDefinition);
    }

    /**
     * Retrieve first payment price
     *
     * @param array $options
     * @return string
     */
    public function getInitialPaymentPrice(array $options)
    {
        $profileDefinition = $options['aw_sarp2_subscription_plan']['definition'];
        $subscriptionOptions = $options['aw_sarp2_subscription_option'];
        $firstPaymentAmount = $profileDefinition['is_trial_period_enabled']
            ? $subscriptionOptions['trial_price']
            : $subscriptionOptions['regular_price'];
        $currencyCode = $subscriptionOptions['currency_code'];
        $fee = $subscriptionOptions['initial_fee'];

        return $this->detailsFormatter->getInitialPaymentPrice(
            $fee,
            $firstPaymentAmount,
            $currencyCode
        );
    }

    /**
     * Retrieve trial price
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param array $options
     * @return string
     */
    public function getTrialPriceAndCycles(PlanDefinitionInterface $planDefinition, array $options)
    {
        $subscriptionOptions = $options['aw_sarp2_subscription_option'];
        return $this->detailsFormatter->getTrialPriceAndCycles(
            $subscriptionOptions['trial_price'],
            $planDefinition,
            true,
            false,
            $subscriptionOptions['currency_code']
        );
    }

    /**
     * Retrieve regular price
     *
     * @param PlanDefinitionInterface $planDefinition
     * @param array $options
     * @return string
     */
    public function getRegularPriceAndCycles(PlanDefinitionInterface $planDefinition, array $options)
    {
        $subscriptionOptions = $options['aw_sarp2_subscription_option'];
        return $this->detailsFormatter->getRegularPriceAndCycles(
            $subscriptionOptions['regular_price'],
            $planDefinition,
            true,
            false,
            $subscriptionOptions['currency_code']
        );
    }

    /**
     * Retrieve trial start date
     *
     * @param int $profileId
     * @return string
     * @throws LocalizedException
     */
    public function getTrialStartDate(int $profileId)
    {
        return $this->formatDate(
            $this->profileDateResolver->getTrialStartDate($profileId, true)
        );
    }

    /**
     * Retrieve regular start date
     *
     * @param int $profileId
     * @return string
     * @throws LocalizedException
     */
    public function getRegularStartDate(int $profileId)
    {
        return $this->formatDate(
            $this->profileDateResolver->getRegularStartDate($profileId, true)
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

    /**
     * Retrieve profile definition by profile item
     *
     * @param ProfileItem $item
     * @return PlanDefinitionInterface
     * @throws LocalizedException
     */
    private function getProfileDefinition($item) {
        return $this->profileRepository
            ->get($item->getProfileId())
            ->getProfileDefinition();
    }
}
