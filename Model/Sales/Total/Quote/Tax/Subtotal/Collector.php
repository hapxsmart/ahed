<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\Subtotal;

use Aheadworks\Sarp2\Model\Sales\Total\GroupInterface;
use Aheadworks\Sarp2\Model\Sales\Total\PopulatorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\Keyer;
use Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails\Builder;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
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
     * @var Keyer
     */
    private $keyer;

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
     * @param Keyer $keyer
     * @param Factory $dataObjectFactory
     * @param Summator $grandSummator
     */
    public function __construct(
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        Builder $quoteDetailsBuilder,
        GroupInterface $totalsGroup,
        Keyer $keyer,
        Factory $dataObjectFactory,
        Summator $grandSummator
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->totalsGroup = $totalsGroup;
        $this->keyer = $keyer;
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
            $isPriceIncludesTax = $this->taxConfig->priceIncludesTax($storeId);

            $quoteDetails = $this->quoteDetailsBuilder->setItemsType('product')
                ->setShippingAssignment($shippingAssignment)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->build();
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemsType('product')
                ->setShippingAssignment($shippingAssignment)
                ->setTotalsProvider($this->totalsGroup->getProvider())
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->build();
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);

            $this->processTaxDetails($taxDetails, $quote, $shippingAssignment, false)
                ->processTaxDetails($baseTaxDetails, $quote, $shippingAssignment, true);
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $quote, $shippingAssignment, $isBaseCurrency)
    {
        $subtotal = 0;
        $tax = 0;
        $subtotalInclTax = 0;

        $currencyOption = $isBaseCurrency
            ? PopulatorInterface::CURRENCY_OPTION_USE_BASE
            : PopulatorInterface::CURRENCY_OPTION_USE_STORE;
        $currencyForConvert = $quote->getForcedCurrency()
            ? $quote->getForcedCurrency()->getCode()
            : null;

        /** @var Item $keyedItems */
        $keyedItems = $this->keyer->keyBy($shippingAssignment->getItems(), 'getTaxCalculationItemId');
        foreach ($taxDetails->getItems() as $item) {
            if (isset($keyedItems[$item->getCode()])) {
                $quoteItem = $keyedItems[$item->getCode()];
                if ($item->getType() == 'product') {
                    $this->populate($quoteItem, $item, $currencyOption, $currencyForConvert);
                    $parentQuoteItem = $quoteItem->getParentItem();
                    if ($parentQuoteItem) {
                        $this->populate($parentQuoteItem, $item, $currencyOption, $currencyForConvert);
                    }

                    if (!($quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated())) {
                        $subtotal += $item->getRowTotal();
                        $tax += $item->getRowTax();
                        $subtotalInclTax += $item->getRowTotalInclTax();
                    }
                }
            }
        }
        $this->totalsGroup->getPopulator(CartInterface::class)
            ->populate(
                $quote,
                $this->dataObjectFactory->create(['subtotal' => $subtotal]),
                $currencyOption,
                $currencyForConvert
            );
        $this->totalsGroup->getPopulator(AddressInterface::class)
            ->populate(
                $shippingAssignment->getShipping()->getAddress(),
                $this->dataObjectFactory->create(
                    [
                        'subtotal' => $subtotal,
                        'subtotal_incl_tax' => $subtotalInclTax,
                        'tax' => $tax
                    ]
                ),
                $currencyOption,
                $currencyForConvert
            );
        if ($isBaseCurrency) {
            $this->grandSummator->setAmount(
                $this->totalsGroup->getCode() . '_subtotal',
                $subtotalInclTax
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

    /**
     * Populate quote item
     *
     * @param Item $quoteItem
     * @param TaxDetailsItemInterface $item
     * @param string $currencyOption
     * @param string $currency
     */
    private function populate($quoteItem, $item, $currencyOption, $currency)
    {
        $this->totalsGroup->getPopulator(CartItemInterface::class)
            ->populate(
                $quoteItem,
                $this->dataObjectFactory->create(
                    [
                        'price' => $item->getPrice(),
                        'price_incl_tax' => $item->getPriceInclTax(),
                        'row_total' => $item->getRowTotal(),
                        'row_total_incl_tax' => $item->getRowTotalInclTax(),
                        'row_total_tax' => $item->getRowTax(),
                        'tax_percent' => $item->getTaxPercent()
                    ]
                ),
                $currencyOption,
                $currency
            );
    }
}
