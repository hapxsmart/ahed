<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Plan\Period\Formatter as PeriodFormatter;
use Aheadworks\Sarp2\Model\Plan\Source\BillingPeriod;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Calculator\CatalogPriceCalculator;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator\OptionProvider as OptionProviderPool;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationInputFactory;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class AsLowAsSubscriptionPriceCalculator
 *
 * @package Aheadworks\Sarp2\Model\Product\Subscription\Price
 */
class AsLowAsCalculator
{
    /**
     * @var OptionProviderPool
     */
    private $optionProvider;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var CalculationInputFactory
     */
    private $calculationInputFactory;

    /**
     * @var SubscriptionPriceCalculatorInterface
     */
    private $subscriptionPriceCalculation;

    /**
     * @var CatalogPriceCalculator
     */
    private $catalogPriceCalculator;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * @var PeriodFormatter
     */
    private $periodFormatter;

    /**
     * @var int[]
     */
    private $periodInDaysMap = [
        BillingPeriod::DAY => 1,
        BillingPeriod::WEEK => 7,
        BillingPeriod::SEMI_MONTH => 15,
        BillingPeriod::MONTH => 30,
        BillingPeriod::YEAR => 360,
    ];

    /**
     * @param OptionProviderPool $optionProvider
     * @param PlanRepositoryInterface $planRepository
     * @param CalculationInputFactory $calculationInputFactory
     * @param SubscriptionPriceCalculatorInterface $priceCalculation
     * @param CatalogPriceCalculator $catalogPriceCalculator
     * @param TaxHelper $taxHelper
     * @param PeriodFormatter $periodFormatter
     * @param array $periodInDaysMap
     */
    public function __construct(
        OptionProviderPool $optionProvider,
        PlanRepositoryInterface $planRepository,
        CalculationInputFactory $calculationInputFactory,
        SubscriptionPriceCalculatorInterface $priceCalculation,
        CatalogPriceCalculator $catalogPriceCalculator,
        TaxHelper $taxHelper,
        PeriodFormatter $periodFormatter,
        $periodInDaysMap = []
    ) {
        $this->optionProvider = $optionProvider;
        $this->planRepository = $planRepository;
        $this->calculationInputFactory = $calculationInputFactory;
        $this->subscriptionPriceCalculation = $priceCalculation;
        $this->catalogPriceCalculator = $catalogPriceCalculator;
        $this->taxHelper = $taxHelper;
        $this->periodFormatter = $periodFormatter;
        $this->periodInDaysMap = array_merge($this->periodInDaysMap, $periodInDaysMap);
    }

    /**
     * Calculate As Low As subscription price
     *
     * @param int $productId
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function calculate($productId)
    {
        $asLowAsPricePerDay = INF;
        $asLowAsPricePeriodDaysCount = INF;
        $optionWithMinimalPeriodLength = null;
        $optionWithMinimalPrice = null;
        $wasFound = false;

        $subscriptionOptions = $this->optionProvider->getAllSubscriptionOptions($productId);
        foreach ($subscriptionOptions as $subscriptionOption) {
            $subscriptionPrice = $this->getSubscriptionRegularPrice($subscriptionOption);
            $periodDaysCount = $this->getOptionPeriodDaysCount($subscriptionOption);
            $subscriptionPricePerDay = $subscriptionPrice / $periodDaysCount;

            if ($subscriptionPricePerDay < $asLowAsPricePerDay) {
                $asLowAsPricePerDay = $subscriptionPricePerDay;
                $optionWithMinimalPrice = $subscriptionOption;
                $wasFound = true;
            }
            if ($periodDaysCount < $asLowAsPricePeriodDaysCount) {
                $asLowAsPricePeriodDaysCount = $periodDaysCount;
                $optionWithMinimalPeriodLength = $subscriptionOption;
            }
        }

        if ($wasFound) {
            $minimalPeriodDaysCount = $this->getOptionPeriodDaysCount($optionWithMinimalPeriodLength);
            $price = $asLowAsPricePerDay * $minimalPeriodDaysCount;

            return $this->prepareResult(
                $price,
                $optionWithMinimalPeriodLength,
                $optionWithMinimalPrice
            );
        }

        return null;
    }

    /**
     * Calculate subscription regular price
     *
     * @param SubscriptionOptionInterface $option
     * @return float
     */
    private function getSubscriptionRegularPrice($option)
    {
        $productId = $option->getProduct()->getId();
        $baseRegularPrice = $this->subscriptionPriceCalculation->getRegularPrice(
            $this->calculationInputFactory->create($option->getProduct(), 1),
            $option
        );

        return $this->catalogPriceCalculator->getFinalPriceAmount(
            $productId,
            $baseRegularPrice,
            $this->taxHelper->displayPriceExcludingTax()
        );
    }

    /**
     * Calculate option days count
     *
     * @param SubscriptionOptionInterface $option
     * @return float
     */
    private function getOptionPeriodDaysCount($option)
    {
        $plan = $this->getPlan($option->getPlanId());
        $period = $plan->getDefinition()->getBillingPeriod();
        $frequency = $plan->getDefinition()->getBillingFrequency();

        $daysCount = $this->periodInDaysMap[$period];

        return $daysCount * $frequency;
    }

    /**
     * Retrieve plan by plan id
     *
     * @param $planId
     * @return \Aheadworks\Sarp2\Api\Data\PlanInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPlan($planId)
    {
        return $this->planRepository->get($planId);
    }

    /**
     * Prepare result
     *
     * @param float $price
     * @param SubscriptionOptionInterface $optionWithMinimalPeriodLength
     * @param SubscriptionOptionInterface $optionWithMinimalPrice
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function prepareResult($price, $optionWithMinimalPeriodLength, $optionWithMinimalPrice) {
        $plan = $this->getPlan($optionWithMinimalPeriodLength->getPlanId());
        $frequency = $plan->getDefinition()->getBillingFrequency();
        $period = $plan->getDefinition()->getBillingPeriod();
        $formattedPeriod = $this->periodFormatter->formatPeriod(
            $period,
            $frequency
        );

        return [
            'price' => [
                'finalPrice' => [
                    'amount' => $price,
                    'aw_period' => $formattedPeriod,
                ]
            ],
            'subscriptionOptionId' => $optionWithMinimalPrice->getOptionId()
        ];
    }
}
