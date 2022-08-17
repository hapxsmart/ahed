<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector;

use Aheadworks\Sarp2\Model\Profile\Merged\Set\DataResolver;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\CollectorInterface;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Grand\Summator;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails\Builder;
use Aheadworks\Sarp2\Model\Sales\Total\Merged\Subject;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Model\Config;

/**
 * Class TaxShipping
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector
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
     * @var DataResolver
     */
    private $dataResolver;

    /**
     * @var Summator
     */
    private $grandSummator;

    /**
     * @param TaxCalculationInterface $taxCalculation
     * @param Config $taxConfig
     * @param Builder $quoteDetailsBuilder
     * @param DataResolver $dataResolver
     * @param Summator $grandSummator
     */
    public function __construct(
        TaxCalculationInterface $taxCalculation,
        Config $taxConfig,
        Builder $quoteDetailsBuilder,
        DataResolver $dataResolver,
        Summator $grandSummator
    ) {
        $this->taxCalculation = $taxCalculation;
        $this->taxConfig = $taxConfig;
        $this->quoteDetailsBuilder = $quoteDetailsBuilder;
        $this->dataResolver = $dataResolver;
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
            $isPriceIncludesTax = $this->taxConfig->shippingPriceIncludesTax($storeId);
            $taxClass = $this->taxConfig->getShippingTaxClass($storeId);

            $quoteDetails = $this->quoteDetailsBuilder->setItemType('shipping')
                ->setPaymentsInfo($subject->getPaymentsInfo())
                ->setItemPairs($subject->getItemPairs())
                ->setIsBaseCurrency(false)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->setStoreId($storeId)
                ->setShippingAmount($subject->getOrder()->getShippingAmount())
                ->build();
            if ($quoteDetails) {
                $taxDetails = $this->taxCalculation->calculateTax($quoteDetails, $storeId);
                $this->processTaxDetails($taxDetails, $subject, false);
            }

            $baseQuoteDetails = $this->quoteDetailsBuilder->setItemType('shipping')
                ->setPaymentsInfo($subject->getPaymentsInfo())
                ->setItemPairs($subject->getItemPairs())
                ->setIsBaseCurrency(true)
                ->setIsPriceIncludesTax($isPriceIncludesTax)
                ->setTaxClass($taxClass)
                ->setStoreId($storeId)
                ->setShippingAmount($subject->getOrder()->getBaseShippingAmount())
                ->build();
            if ($baseQuoteDetails) {
                $baseTaxDetails = $this->taxCalculation->calculateTax($baseQuoteDetails, $storeId);
                $this->processTaxDetails($baseTaxDetails, $subject, true);
            }
        }
    }

    /**
     * Process tax details
     *
     * @param TaxDetailsInterface $taxDetails
     * @param Subject $subject
     * @param bool $isBaseCurrency
     * @return $this
     */
    private function processTaxDetails($taxDetails, $subject, $isBaseCurrency)
    {
        $taxDetailsItem = $taxDetails->getItems()['shipping'];
        $order = $subject->getOrder();
        if ($isBaseCurrency) {
            $baseTaxAmount = $order->getBaseTaxAmount() + $taxDetailsItem->getRowTax();
                $order->setBaseShippingAmount($taxDetailsItem->getRowTotal())
                ->setBaseShippingInclTax($taxDetailsItem->getRowTotalInclTax())
                ->setBaseShippingTaxAmount($taxDetailsItem->getRowTax())
                ->setBaseTaxAmount($baseTaxAmount);
            $this->grandSummator->setTotalAmount('shipping', $taxDetailsItem->getRowTotalInclTax());
        } else {
            $taxAmount = $order->getTaxAmount() + $taxDetailsItem->getRowTax();
            $order->setShippingAmount($taxDetailsItem->getRowTotal())
                ->setShippingInclTax($taxDetailsItem->getRowTotalInclTax())
                ->setShippingTaxAmount($taxDetailsItem->getRowTax())
                ->setTaxAmount($taxAmount);
        }
        return $this;
    }
}
