<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails\Builder;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider\AbstractProvider;
use Magento\Framework\DataObject\Factory;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

/**
 * Class TaxShipping
 */
class TaxShipping implements CollectorInterface
{
    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculation;

    /**
     * @var Config
     */
    private $taxConfig;

    /**
     * @var Builder
     */
    private $quoteDetailsBuilder;

    /**
     * @var GroupInterface
     */
    private $totalsGroup;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param TaxCalculationInterface $taxCalculation
     * @param Config $taxConfig
     * @param Builder $quoteDetailsBuilder
     * @param GroupInterface $totalsGroup
     * @param Factory $dataObjectFactory
     * @param Summator $grandSummator
     */
    public function __construct(
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        Builder $quoteDetailsBuilder,
        GroupInterface $totalsGroup,
        Factory $dataObjectFactory,
        Summator $grandSummator
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->totalsGroup = $totalsGroup;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ProfileInterface $profile)
    {
        $items = $profile->getItems();
        if ($items) {
            $storeId = $profile->getStoreId();
            $isPriceIncludesTax = $this->taxConfig->shippingPriceIncludesTax($storeId);
            $taxClass = $this->taxConfig->getShippingTaxClass($storeId);

            /** @var AbstractProvider $totalsProvider */
            $totalsProvider = $this->totalsGroup->getProvider();
            $totalsProvider->setProfile($profile);

            $quoteDetails = $this->quoteDetailsBuilder->setItemType('shipping')
                ->setProfile($profile)
                ->setTotalsProvider($totalsProvider)
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->build();
            if ($quoteDetails) {
                $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);
                $this->processTaxDetails($taxDetails, $profile, false);
            }

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemType('shipping')
                ->setProfile($profile)
                ->setTotalsProvider($totalsProvider)
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->build();
            if ($baseQuoteDetails) {
                $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);
                $this->processTaxDetails($baseTaxDetails, $profile, true);
            }
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param ProfileInterface $profile
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $profile, $isBaseCurrency)
    {
        $taxDetailsItem = $taxDetails->getItems()['shipping'];
        $tax = $this->getTaxAmount($profile, $isBaseCurrency);
        $this->totalsGroup->getPopulator(ProfileInterface::class)
            ->populate(
                $profile,
                $this->dataObjectFactory->create(
                    [
                        'shipping_amount' => $taxDetailsItem->getRowTotal(),
                        'shipping_amount_incl_tax' => $taxDetailsItem->getRowTotalInclTax(),
                        'shipping_tax_amount' => $taxDetailsItem->getRowTax(),
                        'tax' => $tax + $taxDetailsItem->getRowTax()
                    ]
                ),
                $isBaseCurrency
                    ? PopulatorInterface::CURRENCY_OPTION_USE_BASE
                    : PopulatorInterface::CURRENCY_OPTION_USE_STORE,
                $profile->getProfileCurrencyCode()
            );
        if ($isBaseCurrency) {
            $this->grandSummator->setAmount(
                $this->totalsGroup->getCode() . '_shipping',
                $taxDetailsItem->getRowTotalInclTax()
            );
        }
        return $this;
    }

    /**
     * Get current tax amount
     *
     * @param ProfileInterface $profile
     * @param bool $isBaseCurrency
     * @return float
     */
    private function getTaxAmount($profile, $isBaseCurrency)
    {
        $code = $this->totalsGroup->getCode();
        $prefix = $isBaseCurrency ? 'base_' : '';

        return $profile->getData($prefix . $code . '_tax_amount');
    }
}
