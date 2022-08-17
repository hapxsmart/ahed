<?php
namespace Aheadworks\Sarp2\ViewModel\Subscription\Details;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Plan\DateResolver as PlanDateResolver;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter as DetailsFormatter;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Tax\Model\Config as TaxConfig;

class ForQuoteItem implements ArgumentInterface
{
    /**
     * @var DetailsFormatter
     */
    private $detailsFormatter;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionRepository;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var TaxConfig
     */
    private $taxConfig;

    /**
     * @var PlanDateResolver
     */
    private $planDateResolver;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @param DetailsFormatter $detailsFormatter
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param PlanRepositoryInterface $planRepository
     * @param TaxConfig $taxConfig
     * @param PlanDateResolver $planDateResolver
     * @param PriceCurrency $priceCurrency
     */
    public function __construct(
        DetailsFormatter $detailsFormatter,
        SubscriptionOptionRepositoryInterface $optionRepository,
        PlanRepositoryInterface $planRepository,
        TaxConfig $taxConfig,
        PlanDateResolver $planDateResolver,
        PriceCurrency $priceCurrency
    ) {
        $this->detailsFormatter = $detailsFormatter;
        $this->optionRepository = $optionRepository;
        $this->planRepository = $planRepository;
        $this->taxConfig = $taxConfig;
        $this->planDateResolver = $planDateResolver;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Retrieve plan name. Not title!
     *
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getPlanName($item)
    {
        return $this->getPlan($item)->getName();
    }

    /**
     * Check if show initial payment details
     *
     * @param ItemInterface $item
     * @return bool
     * @throws LocalizedException
     */
    public function isShowInitialDetails($item)
    {
        return $this->detailsFormatter->isShowInitialDetails(
            $this->getPlanDefinition($item)
        );
    }

    /**
     * Check if show trial period details
     *
     * @param ItemInterface $item
     * @return bool
     * @throws LocalizedException
     */
    public function isShowTrialDetails($item)
    {
        return $this->detailsFormatter->isShowTrialDetails(
            $this->getPlanDefinition($item)
        );
    }

    /**
     * Check if show regular period details
     *
     * @param ItemInterface $item
     * @return bool
     * @throws LocalizedException
     */
    public function isShowRegularDetails($item)
    {
        return $this->detailsFormatter->isShowRegularDetails(
            $this->getPlanDefinition($item)
        );
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
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getTrialLabel($item)
    {
        return $this->detailsFormatter->getTrialOfferLabel(
            $this->getPlanDefinition($item),
            $this->getAmount($item->getBaseAwSarpTrialPrice(), $item->getBaseAwSarpTrialPriceInclTax())
        );
    }

    /**
     * Retrieve regular label
     *
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getRegularLabel($item)
    {
        return $this->detailsFormatter->getRegularOfferLabel(
            $this->getPlanDefinition($item)
        );
    }

    /**
     * Retrieve first payment price
     *
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getInitialPaymentPrice($item)
    {
        $planDefinition = $this->getPlanDefinition($item);
        $fee = $this->getAmount($item->getBaseAwSarpInitialFee(), $item->getBaseAwSarpInitialFeeInclTax());
        $firstPaymentAmount = $planDefinition->getIsTrialPeriodEnabled()
            ? $this->getAmount($item->getBaseAwSarpTrialPrice(), $item->getBaseAwSarpTrialPriceInclTax())
            : $this->getAmount($item->getBaseAwSarpRegularPrice(), $item->getBaseAwSarpRegularPriceInclTax());

        return $this->detailsFormatter->getInitialPaymentPrice($fee, $firstPaymentAmount);
    }

    /**
     * Retrieve trial price
     *
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getTrialPriceAndCycles($item)
    {
        return $this->detailsFormatter->getTrialPriceAndCycles(
            $this->getAmount($item->getBaseAwSarpTrialPrice(), $item->getBaseAwSarpTrialPriceInclTax()),
            $this->getPlanDefinition($item)
        );
    }

    /**
     * Retrieve regular price
     *
     * @param ItemInterface $item
     * @return string
     * @throws LocalizedException
     */
    public function getRegularPriceAndCycles($item)
    {
        return $this->detailsFormatter->getRegularPriceAndCycles(
            $this->getAmount($item->getBaseAwSarpRegularPrice(), $item->getBaseAwSarpRegularPriceInclTax()),
            $this->getPlanDefinition($item)
        );
    }

    /**
     * Get subscription ends date label
     *
     * @param ItemInterface $item
     * @return Phrase
     */
    public function getSubscriptionEndsDateLabel($item)
    {
        $planDefinition = $this->getPlanDefinition($item);
        return $this->detailsFormatter->getSubscriptionEndsDateLabel($planDefinition);
    }

    /**
     * Retrieve stop date
     *
     * @param ItemInterface $item
     * @return string
     */
    public function getSubscriptionEndsDate($item)
    {
        $planDefinition = $this->getPlanDefinition($item);

        $startDate = $this->planDateResolver->getStartDate($planDefinition->getStartDateType());
        $stopDate = $this->planDateResolver->getStopDate(
            $startDate,
            $planDefinition,
            true
        );

        $stopDate = $this->detailsFormatter->formatRegularStopDate(
            $stopDate,
            $planDefinition
        );

        return $stopDate;
    }

    /**
     * Retrieve profile plan by profile item
     *
     * @param ItemInterface $item
     * @return PlanInterface
     * @throws LocalizedException
     */
    private function getPlan($item) {
        $itemOption = $item->getOptionByCode('aw_sarp2_subscription_type');
        $subscriptionOption = $this->optionRepository->get($itemOption->getValue());

        return $this->planRepository->get($subscriptionOption->getPlanId());
    }

    /**
     * Retrieve profile definition by profile item
     *
     * @param ItemInterface $item
     * @return PlanDefinitionInterface
     * @throws LocalizedException
     */
    private function getPlanDefinition($item) {
        return $this->getPlan($item)->getDefinition();
    }

    /**
     * Retrieve base or base wit tax amount according magento tax config
     *
     * @param float $base
     * @param float $baseInclTax
     * @return float
     */
    private function getAmount($base, $baseInclTax)
    {
        $amount = $this->taxConfig->priceIncludesTax()
            ? $baseInclTax
            : $base;

        return $this->priceCurrency->convertAndRound($amount);
    }
}
