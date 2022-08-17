<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Price;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Config\AdvancedPricingValueResolver;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\ProductPriceResolver as ProductPriceResolver;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\PeriodPriceCalculator;

/**
 * Class Calculator
 */
class Calculator implements SubscriptionPriceCalculatorInterface
{
    /**
     * @var AdvancedPricingValueResolver
     */
    private $advancedPricingConfigValueResolver;

    /**
     * @var PeriodPriceCalculator
     */
    private $byPeriodCalculator;

    /**
     * @var ProductPriceResolver
     */
    private $productPriceResolver;

    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @param AdvancedPricingValueResolver $advancedPricingConfigValueResolver
     * @param PeriodPriceCalculator $priceCalculator
     * @param ProductPriceResolver $productPriceResolver
     * @param PlanRepositoryInterface $planRepository
     */
    public function __construct(
        AdvancedPricingValueResolver $advancedPricingConfigValueResolver,
        PeriodPriceCalculator $priceCalculator,
        ProductPriceResolver $productPriceResolver,
        PlanRepositoryInterface $planRepository
    ) {
        $this->advancedPricingConfigValueResolver = $advancedPricingConfigValueResolver;
        $this->byPeriodCalculator = $priceCalculator;
        $this->productPriceResolver = $productPriceResolver;
        $this->planRepository = $planRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPrice($calculationInput, $option)
    {
        if ($option->getIsAutoTrialPrice()) {
            $price = $this->byPeriodCalculator->calculateTrialPrice(
                $calculationInput,
                $option->getPlanId()
            );
        } else {
            $price = $option->getTrialPrice();
        }

        return (float)$price;
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPrice($calculationInput, $option)
    {
        if ($option->getIsAutoRegularPrice()) {
            $price = $this->byPeriodCalculator->calculateRegularPrice(
                $calculationInput,
                $option->getPlanId()
            );
        } else {
            $isUseAdvancedPricing = $this->advancedPricingConfigValueResolver->isUsedAdvancePricing(
                $calculationInput->getProduct()->getId()
            );
            if ($isUseAdvancedPricing) {
                $fixedOptionPrice = $option->getRegularPrice();
                $productBasePrice = $this->productPriceResolver->getPrice($calculationInput);
                $price = min($fixedOptionPrice, $productBasePrice);
            } else {
                $price = $option->getRegularPrice();
            }
        }

        return (float)$price;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstPaymentPrice($input, $option)
    {
        $optionPlanId = $option->getPlanId();

        $optionPlanDefinition = $this->planRepository
            ->get($optionPlanId)
            ->getDefinition()
        ;
        $firstPaymentAmount = $optionPlanDefinition->getIsTrialPeriodEnabled()
            ? $this->getTrialPrice($input, $option)
            : $this->getRegularPrice($input, $option)
        ;
        $initialFee = $option->getInitialFee();

        return (float)($initialFee + $firstPaymentAmount);
    }
}
