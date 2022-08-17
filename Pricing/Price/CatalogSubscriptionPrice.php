<?php
namespace Aheadworks\Sarp2\Pricing\Price;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface as PriceCalculation;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Finder as OptionFinder;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationInputFactory;
use Aheadworks\Sarp2\Model\Profile\Details\Formatter;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

/**
 * Class CatalogSubscriptionPrice
 */
class CatalogSubscriptionPrice extends AbstractPrice
{
    /**
     * @var CalculationInputFactory
     */
    protected $calculationInputFactory;

    /**
     * @var PriceCalculation
     */
    protected $priceCalculation;

    /**
     * @var OptionFinder
     */
    protected $finder;

    /**
     * @var PlanRepositoryInterface
     */
    protected $planRepository;

    /**
     * @var Formatter
     */
    protected $detailsFormatter;

    /**
     * @param Product $saleableItem
     * @param float $quantity
     * @param CalculationInputFactory $calculationInputFactory
     * @param Calculator $calculator
     * @param PriceCurrency $priceCurrency
     * @param PriceCalculation $priceCalculation
     * @param PlanRepositoryInterface $planRepository
     * @param Formatter $detailsFormatter
     * @param OptionFinder $finder
     */
    public function __construct(
        Product $saleableItem,
        $quantity,
        CalculationInputFactory $calculationInputFactory,
        Calculator $calculator,
        PriceCurrency $priceCurrency,
        PriceCalculation $priceCalculation,
        PlanRepositoryInterface $planRepository,
        Formatter $detailsFormatter,
        OptionFinder $finder
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->calculationInputFactory = $calculationInputFactory;
        $this->priceCalculation = $priceCalculation;
        $this->finder = $finder;
        $this->planRepository = $planRepository;
        $this->detailsFormatter = $detailsFormatter;
    }

    /**
     * Returns product subscription value
     *
     * @return float|boolean
     * @throws LocalizedException
     */
    public function getValue()
    {
        return $this->getPriceForCatalog();
    }

    /**
     * Get subscription period
     *
     * @throws LocalizedException
     */
    public function getPeriod()
    {
        $subscriptionOption = $this->finder->getFirstOption($this->product->getId());
        if ($subscriptionOption) {
            try {
                $plan = $this->planRepository->get($subscriptionOption->getPlanId());
                return $this->detailsFormatter->getFormattedPeriod($plan->getDefinition());
            } catch (\Exception $exception) {
                return '';
            }
        }

        return '';
    }

    /**
     * Get price for subscription product
     *
     * @return float
     * @throws LocalizedException
     */
    protected function getPriceForCatalog()
    {
        $basePrice = 0;
        $subscriptionOption = $this->finder->getFirstOption($this->product->getId());
        if ($subscriptionOption) {
            $calculationInput = $this->calculationInputFactory->create(
                $subscriptionOption->getProduct(),
                1
            );
            $basePrice = $this->priceCurrency->convert(
                $this->calculatePriceByOption($calculationInput, $subscriptionOption)
            );
        }

        return $basePrice;
    }

    /**
     * Get base regular price for option
     *
     * @param CalculationInput $calculationInput
     * @param SubscriptionOptionInterface $subscriptionOption
     * @return float
     * @throws NoSuchEntityException
     */
    protected function calculatePriceByOption($calculationInput, $subscriptionOption)
    {
        return $this->priceCalculation->getRegularPrice(
            $calculationInput,
            $subscriptionOption
        );
    }
}
