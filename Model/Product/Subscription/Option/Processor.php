<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Option;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Aheadworks\Sarp2\Model\Plan\DateResolver as PlanDateResolver;
use Aheadworks\Sarp2\Model\Plan\Source\FrontendDisplayingMode;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Calculator\CatalogPriceCalculator;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Calculator\TierPriceCalculator;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\BuyRequestProductConfigurator;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter as DetailsFormatter;
use Aheadworks\Sarp2\Model\Profile\Item as ProfileItem;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group\CustomOptionCalculator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Locale\Format as LocaleFormat;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Processor
{
    /**#@+
     * Constants for detailed options
     */
    const LABEL = 'label';
    const FIRST = 'first_payment';
    const TRIAL = 'trial_payment';
    const REGULAR = 'regular_payment';
    const ENDS = 'subscription_ends';
    /**#@-*/

    /**
     * @var SubscriptionPriceCalculatorInterface
     */
    private $subscriptionPriceCalculation;

    /**
     * @var LocaleFormat
     */
    private $localeFormat;

    /**
     * @var CatalogPriceCalculator
     */
    private $catalogPriceCalculator;

    /**
     * @var CustomOptionCalculator
     */
    private $customOptionCalculator;

    /**
     * @var PlanDateResolver
     */
    private $planDateResolver;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var DetailsFormatter
     */
    private $detailsFormatter;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TierPriceCalculator
     */
    private $tierPriceCalculation;

    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingConfigValueResolver;

    /**
     * @var Factory
     */
    private $calculationInputFactory;

    /**
     * @var BuyRequestProductConfigurator
     */
    private $buyRequestConfigurator;

    /**
     * Processor constructor.
     *
     * @param SubscriptionPriceCalculatorInterface $priceCalculation
     * @param LocaleFormat $localeFormat
     * @param CatalogPriceCalculator $catalogPriceCalculator
     * @param CustomOptionCalculator $customOptionCalculator
     * @param PlanDateResolver $planDateResolver
     * @param TimezoneInterface $localeDate
     * @param DetailsFormatter $detailsFormatter
     * @param Config $config
     * @param TierPriceCalculator $tierPriceProcessor
     * @param AdvancedPricingValueResolver $advancedPricingConfigValueResolver
     * @param Factory $calculationInputFactory
     * @param BuyRequestProductConfigurator $buyRequestConfigurator
     */
    public function __construct(
        SubscriptionPriceCalculatorInterface $priceCalculation,
        LocaleFormat $localeFormat,
        CatalogPriceCalculator $catalogPriceCalculator,
        CustomOptionCalculator $customOptionCalculator,
        PlanDateResolver $planDateResolver,
        TimezoneInterface $localeDate,
        DetailsFormatter $detailsFormatter,
        Config $config,
        TierPriceCalculator $tierPriceProcessor,
        AdvancedPricingValueResolver $advancedPricingConfigValueResolver,
        Factory $calculationInputFactory,
        BuyRequestProductConfigurator $buyRequestConfigurator
    ) {
        $this->subscriptionPriceCalculation = $priceCalculation;
        $this->localeFormat = $localeFormat;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
        $this->customOptionCalculator = $customOptionCalculator;
        $this->planDateResolver = $planDateResolver;
        $this->localeDate = $localeDate;
        $this->detailsFormatter = $detailsFormatter;
        $this->config = $config;
        $this->tierPriceCalculation = $tierPriceProcessor;
        $this->advancedPricingConfigValueResolver = $advancedPricingConfigValueResolver;
        $this->calculationInputFactory = $calculationInputFactory;
        $this->buyRequestConfigurator = $buyRequestConfigurator;
    }

    /**
     * Get detailed options
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param Profile|null $profile
     * @param ProfileItem|null $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function getDetailedOptions($option, $planDefinition, $exclTax = true, $profile = null, $item = null)
    {
        $details = [
            self::LABEL => $this->getLabel($planDefinition),
            self::FIRST => $this->getFirstPayment($option, $planDefinition, $exclTax, $item),
            self::TRIAL => $this->getTrialPayments($option, $planDefinition, $exclTax, $profile, $item),
            self::REGULAR => $this->getRegularPayments($option, $planDefinition, $exclTax, $profile, $item),
            self::ENDS => $this->getSubscriptionEndsDate($planDefinition)
        ];

        return array_filter($details, function($detailsItem) {
            return $detailsItem['isShow'];
        });
    }

    /**
     * Calculate base amount of the first payment
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param ProfileItem|null $item
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFirstPaymentBaseAmount($option, $planDefinition, $item = null)
    {
        $firstPaymentAmount = $planDefinition->getIsTrialPeriodEnabled()
            ? $this->getBaseTrialPrice($option, $item)
            : $this->getBaseRegularPrice($option, $item);

        return (float)($firstPaymentAmount);
    }

    /**
     * Calculate final base amount of the first payment
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param ProfileItem|null $item
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFirstPaymentFullBaseAmount($option, $planDefinition, $item = null)
    {
        $initialFee = $option->getInitialFee();
        $firstPaymentBaseAmount = $this->getFirstPaymentBaseAmount(
            $option,
            $planDefinition,
            $item
        );

        return (float)($initialFee + $firstPaymentBaseAmount);
    }

    /**
     * Get label
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return array
     */
    private function getLabel($planDefinition)
    {
        return [
            'isShow' => true,
            'type' => self::LABEL,
            'value' => $planDefinition->getFrontendDisplayingMode() == FrontendDisplayingMode::INSTALLMENT
                ? __('Installment details')
                : __('Subscription details')
        ];
    }

    /**
     * Get first payment
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param ProfileItem|null $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFirstPayment($option, $planDefinition, $exclTax, $item = null)
    {
        $data = [
            'isShow' => false,
            'type' => self::FIRST,
            'finalFee' => 0,
            'finalAmount' => 0,
            'finalTrialAmount' => null,
            'formattedFee' => '',
            'formattedAmount' => '',
            'label' => '',
            'value' => ''
        ];

        $isShow = $this->detailsFormatter->isShowInitialDetails($planDefinition);
        if ($isShow) {

            $firstPaymentFullBaseAmount = $this->getFirstPaymentFullBaseAmount(
                $option,
                $planDefinition,
                $item
            );

            $finalFee = $this->catalogPriceCalculator->getFinalPriceAmount(
                $option->getProduct()->getId(),
                $option->getInitialFee(),
                $exclTax
            );
            $finalAmount = $this->catalogPriceCalculator->getFinalPriceAmount(
                $option->getProduct()->getId(),
                $firstPaymentFullBaseAmount,
                $exclTax
            );
            $formattedAmount = $this->catalogPriceCalculator->getFormattedPrice(
                $option->getProduct()->getId(),
                $finalAmount,
                $exclTax
            );
            $formattedFee = $this->catalogPriceCalculator->getFormattedPrice(
                $option->getProduct()->getId(),
                $option->getInitialFee(),
                $exclTax
            );

            $finalTrialAmount = null;
            if ($planDefinition->getIsTrialPeriodEnabled()
                && $planDefinition->getTrialTotalBillingCycles() == 1
            ) {
                $finalTrialAmount = $this->catalogPriceCalculator->getFinalPriceAmount(
                    $option->getProduct()->getId(),
                    $this->getBaseTrialPrice($option, $item),
                    $exclTax
                );
            }

            $data = [
                'isShow' => $isShow,
                'type' => self::FIRST,
                'finalFee' => $finalFee,
                'finalAmount' => $finalAmount,
                'finalTrialAmount' => $finalTrialAmount,
                'formattedFee' => $formattedFee,
                'formattedAmount' => $formattedAmount,
                'label' => $this->detailsFormatter->getInitialPaymentLabel(),
                'value' => $this->detailsFormatter->getInitialPaymentPrice(
                    $finalFee,
                    $this->getFirstPaymentBaseAmount(
                        $option,
                        $planDefinition,
                        $item
                    )
                )
            ];
        }

        return $data;
    }

    /**
     * Get formatted trial payments
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param Profile|null $profile
     * @param ProfileItem|null $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    private function getTrialPayments($option, $planDefinition, $exclTax, $profile = null, $item = null)
    {
        $data = [
            'isShow' => false,
            'type' => self::TRIAL,
            'finalAmount' => 0,
            'formattedAmount' => '',
            'cycles' => null,
            'formattedCycles' => null,
            'startingFrom' => null,
            'label' => '',
            'value' => ''
        ];

        $forChangePlan = $profile != null;
        $isShow = $this->detailsFormatter->isShowTrialDetails($planDefinition, $forChangePlan);
        if ($isShow) {
            $amount = $this->getBaseTrialPrice($option, $item);
            $currency = $this->getCurrency($profile);

            $finalAmount = $this->catalogPriceCalculator->getFinalPriceAmount(
                $option->getProduct()->getId(),
                $amount,
                $exclTax,
                $currency
            );
            $formattedAmount = $amount == 0
                ? __('Free')
                : $this->catalogPriceCalculator->getFormattedPrice(
                    $option->getProduct()->getId(),
                    $amount,
                    $exclTax,
                    $currency
                );

            $trialCycles = $planDefinition->getTrialTotalBillingCycles();
            $skipInitialCycle = false;
            if ($skipInitialCycle = !$forChangePlan && $planDefinition->getIsInitialFeeEnabled()) {
                $trialCycles--;
            }

            $formattedCycles = $formattedCyclesAfter = $this->detailsFormatter->getTrialPriceAndCycles(
                $amount,
                $planDefinition,
                $skipInitialCycle,
                true,
                $currency
            );
            $formattedCyclesBefore = null;
            $formattedCyclesAfter = null;
            if ($this->config->isAlternativeSubscriptionDetailsView()) {
                $formattedCyclesAfter = $formattedCycles;
            } else {
                $formattedCyclesBefore = $formattedCycles;
            }

            $label = $this->detailsFormatter->getTrialOfferLabel($planDefinition, $finalAmount, $skipInitialCycle);

            $startingFrom = $this->planDateResolver->getTrialStartDate(
                $this->getStartDate($planDefinition),
                $planDefinition,
                true
            );
            $startingFromFormatted = $this->formatDate($startingFrom);

            $data = [
                'isShow' => $isShow,
                'type' => self::TRIAL,
                'finalAmount' => $finalAmount,
                'formattedAmount' => $formattedAmount,
                'cycles' => (int)$trialCycles,
                'formattedCyclesBefore' => $formattedCyclesBefore,
                'formattedCyclesAfter' => $formattedCyclesAfter,
                'startingFrom' => $startingFromFormatted,
                'label' => $label,
                'value' => $this->detailsFormatter->getTrialPriceAndCycles(
                    $finalAmount,
                    $planDefinition,
                    $skipInitialCycle,
                    false,
                    $currency
                )
            ];
        }

        return $data;
    }

    /**
     * Get regular payments
     *
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @param Profile|null $profile
     * @param ProfileItem|null $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    private function getRegularPayments($option, $planDefinition, $exclTax, $profile = null, $item = null)
    {
        $amount = $this->getBaseRegularPrice($option, $item);
        $currency = $this->getCurrency($profile);

        $finalAmount = $this->catalogPriceCalculator->getFinalPriceAmount(
            $option->getProduct()->getId(),
            $amount,
            $exclTax,
            $currency
        );
        $formattedAmount = $this->catalogPriceCalculator->getFormattedPrice(
            $option->getProduct()->getId(),
            $finalAmount,
            $exclTax,
            $currency
        );

        $forChangePlan = $profile != null;
        $isShow = $this->detailsFormatter->isShowRegularDetails($planDefinition) || $forChangePlan;

        $regularCycles = $planDefinition->getTotalBillingCycles();
        $skipInitialCycle = false;
        if ($skipInitialCycle = $regularCycles > 0
            && $planDefinition->getIsInitialFeeEnabled()
            && !$planDefinition->getIsTrialPeriodEnabled()
            && !$forChangePlan
        ) {
            $regularCycles--;
        }

        $formattedCycles = $formattedCyclesAfter = $this->detailsFormatter->getRegularPriceAndCycles(
            $finalAmount,
            $planDefinition,
            $skipInitialCycle,
            true,
            $currency
        );
        $formattedCyclesBefore = null;
        $formattedCyclesAfter = null;
        if ($this->config->isAlternativeSubscriptionDetailsView()) {
            $formattedCyclesAfter = $formattedCycles;
        } else {
            $formattedCyclesBefore = $formattedCycles;
        }

        $label = $this->detailsFormatter->getRegularOfferLabel($planDefinition, $skipInitialCycle);

        $startingFrom = $this->planDateResolver->getRegularStartDate(
            $this->getStartDate($planDefinition),
            $planDefinition,
            true
        );
        $startingFromFormatted = $this->formatDate($startingFrom);

        $data = [
            'isShow' => $isShow,
            'type' => self::REGULAR,
            'finalAmount' => $finalAmount,
            'formattedAmount' => $formattedAmount,
            'cycles' => (int)$regularCycles,
            'formattedCyclesBefore' => $formattedCyclesBefore,
            'formattedCyclesAfter' => $formattedCyclesAfter,
            'product_id' => (int)$option->getProductId(),
            'startingFrom' => $startingFromFormatted,
            'label' => $label,
            'value' => $this->detailsFormatter->getRegularPriceAndCycles(
                $finalAmount,
                $planDefinition,
                $skipInitialCycle,
                false,
                $currency
            )
        ];

        return $data;
    }

    /**
     * Get subscription ens date
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return array
     * @throws \Exception
     */
    private function getSubscriptionEndsDate($planDefinition)
    {
        $stopDate = $this->planDateResolver->getStopDate(
            $this->getStartDate($planDefinition),
            $planDefinition,
            true
        );

        $stopDate = $this->detailsFormatter->formatRegularStopDate(
            $stopDate,
            $planDefinition
        );

        $data = [
            'isShow' => true,
            'type' => self::ENDS,
            'label' => $this->detailsFormatter->getSubscriptionEndsDateLabel($planDefinition),
            'date' => $stopDate,
            'value' => $stopDate
        ];

        return $data;
    }

    /**
     * Retrieve start subscription date
     *
     * @param PlanDefinitionInterface $planDefinition
     * @return string|null
     */
    private function getStartDate($planDefinition) {
        return $this->planDateResolver->getStartDate($planDefinition->getStartDateType());
    }

    /**
     * Format date
     *
     * @param \DateTime|string $date
     * @param int $format
     * @param bool $showTime
     * @param null $timezone
     * @return string|null
     * @throws \Exception
     */
    public function formatDate(
        $date = null,
        $format = \IntlDateFormatter::MEDIUM,
        $showTime = false,
        $timezone = null
    ) {
        $date = $date instanceof \DateTimeInterface ? $date : new \DateTime($date);
        return $this->localeDate->formatDateTime(
            $date,
            $format,
            $showTime ? $format : \IntlDateFormatter::NONE,
            null,
            $timezone
        );
    }

    /**
     * Get option prices
     *
     * @param float $basePrice
     * @param SubscriptionOptionInterface $option
     * @param PlanDefinitionInterface $planDefinition
     * @param bool $exclTax
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOptionPriceDataList($basePrice, $option, $planDefinition, $exclTax = true)
    {
        $productId = $option->getProduct()->getId();
        if ($this->advancedPricingConfigValueResolver->isUsedAdvancePricing($productId)) {
            $oldPrice = $this->localeFormat->getNumber(
                $option->getProduct()->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue()
            );
            $tierPrices = $this->tierPriceCalculation->calculateSubscriptionTierPrices(
                $option->getProduct(),
                $option
            );
        } else {
            $oldPrice = $this->catalogPriceCalculator->getOldPriceAmount($basePrice);
            $tierPrices = [];
        }

        return [
            'oldPrice' => [
                'amount' => $oldPrice,
            ],
            'basePrice' => [
                'amount' => $this->catalogPriceCalculator->getBasePriceAmount($productId, $basePrice),
            ],
            'finalPrice' => [
                'amount' => $this->catalogPriceCalculator->getFinalPriceAmount($productId, $basePrice, $exclTax),
                'aw_period' => $this->detailsFormatter->getFormattedPeriod($planDefinition)
            ],
            'tierPrices' => $tierPrices,
            'msrpPrice' => ['amount' => null],
        ];
    }

    /**
     * Get product prices
     *
     * @param ProductInterface|Product $product
     * @return array
     */
    public function getProductPrices($product)
    {
        $priceInfo = $product->getPriceInfo();
        $tierPrices = $this->tierPriceCalculation->getRegularTierPrices($product);

        return [
            'oldPrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('regular_price')->getAmount()->getValue()
                ),
            ],
            'basePrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('final_price')->getAmount()->getBaseAmount()
                ),
            ],
            'finalPrice' => [
                'amount' => $this->localeFormat->getNumber(
                    $priceInfo->getPrice('final_price')->getAmount()->getValue()
                ),
            ],
            'tierPrices' => $tierPrices,
        ];
    }

    /**
     * Get base trial price
     *
     * @param SubscriptionOptionInterface $option
     * @param ProfileItem|null $item
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getBaseTrialPrice($option, $item)
    {
        $qty = $item ? $item->getQty() : 1;
        $product = $item ? $this->configureProduct($option->getProduct(), $item) : $option->getProduct();

        $baseTrialPrice = $this->subscriptionPriceCalculation->getTrialPrice(
            $this->calculationInputFactory->create($product, $qty),
            $option
        );

        if ($item) {
            $baseTrialPrice += $this->calculateCustomOptionsPrice($item, $option->getId(), true);
        }

        return $baseTrialPrice;
    }

    /**
     * Get base regular price
     *
     * @param SubscriptionOptionInterface $option
     * @param ProfileItem|null $item
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseRegularPrice($option, $item = null)
    {
        $qty = $item ? $item->getQty() : 1;
        $product = $item ? $this->configureProduct($option->getProduct(), $item) : $option->getProduct();

        $baseRegularPrice = $this->subscriptionPriceCalculation->getRegularPrice(
            $this->calculationInputFactory->create($product, $qty),
            $option
        );

        if ($item) {
            $baseRegularPrice += $this->calculateCustomOptionsPrice($item, $option->getId(), false);
        }

        return $baseRegularPrice;
    }

    /**
     * Perform product configuration
     *
     * @param Product|ProductInterface $product
     * @param ProfileItemInterface $item
     * @return Product
     */
    private function configureProduct($product, $item)
    {
        $buyRequest = $item->getProductOptions()['info_buyRequest'] ?? [];
        $this->buyRequestConfigurator->configure($product, $buyRequest);

        return $product;
    }

    /**
     * Calculate custom option price
     *
     * @param ProfileItem|null $item
     * @param $optionId
     * @param bool $forTrial
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function calculateCustomOptionsPrice($item, $optionId, $forTrial)
    {
        $itemClone = clone $item;

        $productOptions = $itemClone->getProductOptions();
        $productOptions['aw_sarp2_subscription_option']['option_id'] = $optionId;
        $itemClone->setProductOptions($productOptions);

        return $this->customOptionCalculator->applyOptionsPrice($itemClone, 0, true, $forTrial);
    }

    /**
     * Retrieve currency code from profile if it's isset
     *
     * @param Profile $profile
     * @return string|null
     */
    private function getCurrency($profile)
    {
        return $profile
            ? $profile->getProfileCurrencyCode()
            : null;
    }
}
