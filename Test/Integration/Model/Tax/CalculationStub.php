<?php
namespace Aheadworks\Sarp2\Test\Integration\Model\Tax;

use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;

/**
 * Class CalculationStub
 * @package Aheadworks\Sarp2\Test\Integration\Model\Tax
 */
class CalculationStub implements TaxCalculationInterface
{
    /**
     * Tax percent
     */
    const TAX_PERCENT = 10;

    /**
     * @var TaxDetailsInterfaceFactory
     */
    private $taxDetailsFactory;

    /**
     * @var TaxDetailsItemInterfaceFactory
     */
    private $taxDetailsItemFactory;

    /**
     * @param TaxDetailsInterfaceFactory $taxDetailsFactory
     * @param TaxDetailsItemInterfaceFactory $taxDetailsItemFactory
     */
    public function __construct(
        TaxDetailsInterfaceFactory $taxDetailsFactory,
        TaxDetailsItemInterfaceFactory $taxDetailsItemFactory
    ) {
        $this->taxDetailsFactory = $taxDetailsFactory;
        $this->taxDetailsItemFactory = $taxDetailsItemFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculateTax(
        QuoteDetailsInterface $quoteDetails,
        $storeId = null,
        $round = true
    ) {
        /** @var TaxDetailsInterface $taxDetails */
        $taxDetails = $this->taxDetailsFactory->create();
        $taxDetailsItems = [];
        foreach ($quoteDetails->getItems() as $item) {
            $qty = $item->getQuantity();

            $price = $item->getUnitPrice();
            $tax = round($price * self::TAX_PERCENT / 100, 2);
            $rowTotal = $price * $qty;
            $rowTax = $tax * $qty;

            $itemCode = $item->getCode();
            /** @var TaxDetailsItemInterface $taxDetailsItem */
            $taxDetailsItem = $this->taxDetailsItemFactory->create();
            $taxDetailsItem->setCode($itemCode)
                ->setType($item->getType())
                ->setPrice($price)
                ->setPriceInclTax($price + $tax)
                ->setRowTax($rowTax)
                ->setRowTotal($rowTotal)
                ->setRowTotalInclTax($rowTotal + $rowTax)
                ->setTaxPercent(self::TAX_PERCENT);

            $taxDetailsItems[$itemCode] = $taxDetailsItem;
        }
        $taxDetails->setItems($taxDetailsItems);
        return $taxDetails;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDefaultCalculatedRate(
        $productTaxClassID,
        $customerId = null,
        $storeId = null
    ) {
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCalculatedRate(
        $productTaxClassID,
        $customerId = null,
        $storeId = null
    ) {
    }
}
