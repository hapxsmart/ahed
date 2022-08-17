<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Api\SubscriptionPriceCalculatorInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input as CalculationInput;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\Input\Factory as CalculationInputFactory;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\Calculation\BuyRequestProductConfigurator;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsChildrenCalculated;
use Aheadworks\Sarp2\Model\Profile\Item\Options\Extractor as OptionExtractor;
use Aheadworks\Sarp2\Model\Sales\Total\Group\AbstractGroup;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorFactory;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\DataObjectHelper;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;

/**
 * Class AbstractProfileGroup
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Group
 */
abstract class AbstractProfileGroup extends AbstractGroup
{
    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    protected $subscriptionOptionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var BuyRequestProductConfigurator
     */
    protected $buyRequestConfigurator;

    /**
     * @var CustomOptionCalculator
     */
    protected $customOptionCalculator;

    /**
     * @var CalculationInputFactory
     */
    protected $calculationInputFactory;

    /**
     * @var IsChildrenCalculated
     */
    protected $isChildrenCalculatedChecker;

    /**
     * @var OptionExtractor
     */
    protected $optionExtractor;

    /**
     * @param SubscriptionOptionRepositoryInterface $optionRepository
     * @param SubscriptionPriceCalculatorInterface $priceCalculation
     * @param PriceCurrency $priceCurrency
     * @param PopulatorFactory $populatorFactory
     * @param ProviderInterface $provider
     * @param SubscriptionOptionInterfaceFactory $subscriptionOptionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomOptionCalculator $customOptionCalculator
     * @param BuyRequestProductConfigurator $buyRequestConfigurator
     * @param CalculationInputFactory $calculationInputFactory
     * @param IsChildrenCalculated $isChildrenCalculated
     * @param OptionExtractor $subscriptionOptionExtractor
     * @param array $populateMaps
     */
    public function __construct(
        SubscriptionOptionRepositoryInterface $optionRepository,
        SubscriptionPriceCalculatorInterface $priceCalculation,
        PriceCurrency $priceCurrency,
        PopulatorFactory $populatorFactory,
        ProviderInterface $provider,
        SubscriptionOptionInterfaceFactory $subscriptionOptionFactory,
        DataObjectHelper $dataObjectHelper,
        CustomOptionCalculator $customOptionCalculator,
        BuyRequestProductConfigurator $buyRequestConfigurator,
        CalculationInputFactory $calculationInputFactory,
        IsChildrenCalculated $isChildrenCalculated,
        OptionExtractor $subscriptionOptionExtractor,
        array $populateMaps = []
    ) {
        parent::__construct(
            $optionRepository,
            $priceCalculation,
            $priceCurrency,
            $populatorFactory,
            $provider,
            $populateMaps
        );
        $this->subscriptionOptionFactory = $subscriptionOptionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customOptionCalculator = $customOptionCalculator;
        $this->buyRequestConfigurator = $buyRequestConfigurator;
        $this->calculationInputFactory = $calculationInputFactory;
        $this->isChildrenCalculatedChecker = $isChildrenCalculated;
        $this->optionExtractor = $subscriptionOptionExtractor;
    }

    /**
     * Retrieve item option
     *
     * @param Item $item
     * @return \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getItemOption($item)
    {
        return $this->optionExtractor->getSubscriptionOptionFromItem($item);
    }

    /**
     * Create calculation subject from profile item
     *
     * @param ProfileItemInterface $item
     * @return CalculationInput
     */
    protected function createCalculationInput($item)
    {
        if ($item->getParentItem() && $this->isChildrenCalculatedChecker->check($item)) {
            $product = $this->configureProduct($item->getParentItem()->getProduct(), $item->getParentItem());
            $childProduct = $this->configureProduct($item->getProduct(), $item);
            $calculationInput = $this->calculationInputFactory->create(
                $product,
                $item->getParentItem()->getQty(),
                $childProduct,
                $item->getQty()
            );
        } else {
            $product = $this->configureProduct($item->getProduct(), $item);
            $calculationInput = $this->calculationInputFactory->create(
                $product,
                $item->getQty()
            );
        }

        return $calculationInput;
    }

    /**
     * Perform product configuration
     *
     * @param Product $product
     * @param ProfileItemInterface $item
     * @return Product
     */
    private function configureProduct($product, $item)
    {
        $buyRequest = $item->getProductOptions()['info_buyRequest'] ?? [];
        $this->buyRequestConfigurator->configure($product, $buyRequest);

        return $product;
    }
}
