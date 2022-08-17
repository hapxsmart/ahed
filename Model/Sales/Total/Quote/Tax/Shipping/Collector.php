<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\Shipping;

use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails\Builder;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

/**
 * Class Collector
 */
class Collector extends AbstractTotal
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
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $items = $shippingAssignment->getItems();
        if ($items) {
            $storeId = $quote->getStore()->getStoreId();
            $isPriceIncludesTax = $this->taxConfig->shippingPriceIncludesTax($storeId);
            $taxClass = $this->taxConfig->getShippingTaxClass($storeId);
            /** @var Address $address */
            $address = $shippingAssignment->getShipping()->getAddress();

            $quoteDetails = $this->quoteDetailsBuilder->setItemsType('shipping')
                ->setShippingAssignment($shippingAssignment)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->build();
            if ($quoteDetails) {
                $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);
                $this->processTaxDetails($taxDetails, $address, $quote, false);
            }

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemsType('shipping')
                ->setShippingAssignment($shippingAssignment)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->build();
            if ($baseQuoteDetails) {
                $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);
                $this->processTaxDetails($baseTaxDetails, $address, $quote, true);
            }
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param Address $address
     * @param Quote $quote
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $address, $quote, $isBaseCurrency)
    {
        $taxDetailsItem = $taxDetails->getItems()['shipping'];
        $currencyForConvert = $quote->getForcedCurrency()
            ? $quote->getForcedCurrency()->getCode()
            : null;

        $this->totalsGroup->getPopulator(AddressInterface::class)
            ->populate(
                $address,
                $this->dataObjectFactory->create(
                    [
                        'shipping_amount' => $taxDetailsItem->getRowTotal(),
                        'shipping_amount_incl_tax' => $taxDetailsItem->getRowTotalInclTax(),
                        'shipping_tax_amount' => $taxDetailsItem->getRowTax()
                    ]
                ),
                $isBaseCurrency
                    ? PopulatorInterface::CURRENCY_OPTION_USE_BASE
                    : PopulatorInterface::CURRENCY_OPTION_USE_STORE,
                $currencyForConvert
            );
        if ($isBaseCurrency) {
            $this->grandSummator->setAmount(
                $this->totalsGroup->getCode() . '_shipping',
                $taxDetailsItem->getRowTotal()
            );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(Quote $quote, Total $total)
    {
        return null;
    }
}
