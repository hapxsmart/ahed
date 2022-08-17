<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails\Builder;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\Keyer;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Class TaxSubtotal
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
 */
class TaxSubtotal implements CollectorInterface
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
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Keyer
     */
    private $keyer;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param TaxCalculationInterface $taxCalculation
     * @param Config $taxConfig
     * @param Builder $quoteDetailsBuilder
     * @param DataResolver $dataResolver
     * @param Keyer $keyer
     * @param Summator $grandSummator
     */
    public function __construct(
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        Builder $quoteDetailsBuilder,
        DataResolver $dataResolver,
        Keyer $keyer,
        Summator $grandSummator
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->dataResolver = $dataResolver;
        $this->keyer = $keyer;
        $this->grandSummator = $grandSummator;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Subject $subject)
    {
        $pairs = $subject->getItemPairs();
        if ($pairs) {
            $storeId = $this->dataResolver->getStoreId($subject->getPaymentsInfo());
            $isPriceIncludesTax = $this->taxConfig->priceIncludesTax($storeId);

            $quoteDetails = $this->quoteDetailsBuilder->setItemType('product')
                ->setPaymentsInfo($subject->getPaymentsInfo())
                ->setItemPairs($subject->getItemPairs())
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setStoreId($storeId)
                ->build();
            $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemType('product')
                ->setPaymentsInfo($subject->getPaymentsInfo())
                ->setItemPairs($subject->getItemPairs())
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setStoreId($storeId)
                ->build();
            $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);

            $this->processTaxDetails($taxDetails, $subject, $isPriceIncludesTax, false)
                ->processTaxDetails($baseTaxDetails, $subject, $isPriceIncludesTax, true);
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param Subject $subject
     * @param bool $isPriceIncludesTax
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $subject, $isPriceIncludesTax, $isBaseCurrency)
    {
        $subtotal = 0;
        $tax = 0;
        $subtotalInclTax = 0;

        $keyedPairs = $this->keyer->keyPairsBy($subject->getItemPairs(), 'getTaxCalculationItemId');
        foreach ($taxDetails->getItems() as $detailsItem) {
            $pair = $keyedPairs[$detailsItem->getCode()];
            /** @var ProfileItemInterface $profileItem */
            $profileItem = $pair[0]->getItem();
            /** @var OrderItemInterface $orderItem */
            $orderItem = $pair[1];
            if ($detailsItem->getType() == 'product') {
                $originalPrice = $isPriceIncludesTax ? $detailsItem->getPriceInclTax() : $detailsItem->getPrice();
                if ($isBaseCurrency) {
                    $orderItem
                        ->setBaseOriginalPrice($originalPrice)
                        ->setBasePrice($detailsItem->getPrice())
                        ->setBasePriceInclTax($detailsItem->getPriceInclTax())
                        ->setBaseRowTotal($detailsItem->getRowTotal())
                        ->setBaseRowTotalInclTax($detailsItem->getRowTotalInclTax())
                        ->setBaseTaxAmount($detailsItem->getRowTax())
                        ->setTaxPercent($detailsItem->getTaxPercent());
                } else {
                    $orderItem
                        ->setOriginalPrice($originalPrice)
                        ->setPrice($detailsItem->getPrice())
                        ->setPriceInclTax($detailsItem->getPriceInclTax())
                        ->setRowTotal($detailsItem->getRowTotal())
                        ->setRowTotalInclTax($detailsItem->getRowTotalInclTax())
                        ->setTaxAmount($detailsItem->getRowTax());
                }

                if (!$profileItem->getParentItemId()) {
                    $subtotal += $detailsItem->getRowTotal();
                    $tax += $detailsItem->getRowTax();
                    $subtotalInclTax += $detailsItem->getRowTotalInclTax();
                }
            }
        }

        $order = $subject->getOrder();
        if ($isBaseCurrency) {
            $order->setBaseSubtotal($subtotal)
                ->setBaseSubtotalInclTax($subtotalInclTax)
                ->setBaseTaxAmount($tax);
            $this->grandSummator->setTotalAmount('subtotal', $subtotalInclTax);
        } else {
            $order->setSubtotal($subtotal)
                ->setSubtotalInclTax($subtotalInclTax)
                ->setTaxAmount($tax);
        }

        return $this;
    }
}
