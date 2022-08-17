<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails;

use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;

/**
 * Class Builder
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Tax\QuoteDetails
 */
class Builder
{
    /**
     * @var string
     */
    private $itemType;

    /**
     * @var ShippingAssignmentInterface
     */
    private $shippingAssignment;

    /**
     * @var bool
     */
    private $isPriceIncludesTax;

    /**
     * @var bool
     */
    private $isBaseCurrency;

    /**
     * @var int
     */
    private $taxClass;

    /**
     * @var ProviderInterface
     */
    private $totalsProvider;

    /**
     * @var QuoteDetailsInterfaceFactory
     */
    private $quoteDetailsFactory;

    /**
     * @var QuoteDetailsItemInterfaceFactory
     */
    private $quoteDetailsItemFactory;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param Mapper $mapper
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        QuoteDetailsInterfaceFactory $quoteDetailsFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        Mapper $mapper,
        Factory $dataObjectFactory
    ) {
        $this->quoteDetailsFactory = $quoteDetailsFactory;
        $this->quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->mapper = $mapper;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Set items type
     *
     * @param string $itemType
     * @return $this
     */
    public function setItemsType($itemType)
    {
        $this->itemType = $itemType;
        return $this;
    }

    /**
     * Set shipping assignment
     *
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return $this
     */
    public function setShippingAssignment($shippingAssignment)
    {
        $this->shippingAssignment = $shippingAssignment;
        return $this;
    }

    /**
     * Set price includes tax flag
     *
     * @param bool $isPriceIncludesTax
     * @return $this
     */
    public function setIsPriceIncludesTax($isPriceIncludesTax)
    {
        $this->isPriceIncludesTax = $isPriceIncludesTax;
        return $this;
    }

    /**
     * Set use base currency flag
     *
     * @param bool $isBaseCurrency
     * @return $this
     */
    public function setIsBaseCurrency($isBaseCurrency)
    {
        $this->isBaseCurrency = $isBaseCurrency;
        return $this;
    }

    /**
     * Set tax class
     *
     * @param int $taxClass
     * @return $this
     */
    public function setTaxClass($taxClass)
    {
        $this->taxClass = $taxClass;
        return $this;
    }

    /**
     * Set totals provider
     *
     * @param ProviderInterface $totalsProvider
     * @return $this
     */
    public function setTotalsProvider($totalsProvider)
    {
        $this->totalsProvider = $totalsProvider;
        return $this;
    }

    /**
     * Build quote details instance
     *
     * @return QuoteDetailsInterface|null
     */
    public function build()
    {
        $result = null;
        if ($this->isStateValid()) {
            if ($this->itemType == 'product') {
                $result = $this->buildProductQuoteDetails();
            } else {
                $result = $this->buildShippingQuoteDetails();
            }
        }
        $this->resetState();
        return $result;
    }

    /**
     * Build product quote details
     *
     * @return QuoteDetailsInterface
     */
    private function buildProductQuoteDetails()
    {
        $quoteDetails = $this->quoteDetailsFactory->create();
        $itemDetails = $this->buildProductQuoteDetailsItems();
        if ($itemDetails) {
            $quoteDetails = $this->mapper->mapQuote(
                $this->shippingAssignment,
                $itemDetails,
                $quoteDetails
            );
        }
        return $quoteDetails;
    }

    /**
     * Build product quote details items
     *
     * @return QuoteDetailsItemInterface[]
     */
    private function buildProductQuoteDetailsItems()
    {
        $quoteDetailsItems = [];

        $items = $this->shippingAssignment->getItems();
        if (count($items)) {
            /** @var AddressItem $item */
            foreach ($items as $item) {
                if (!$item->getParentItem()) {
                    if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                        $parentDetailsItem = $this->quoteDetailsItemFactory->create();
                        $parentDetailsItem = $this->mapper->mapItem(
                            $item,
                            $parentDetailsItem,
                            $this->isPriceIncludesTax,
                            $this->isBaseCurrency,
                            $this->totalsProvider
                        );
                        $quoteDetailsItems[] = $parentDetailsItem;
                        /** @var AddressItem $child */
                        foreach ($item->getChildren() as $child) {
                            $childDetailsItem = $this->quoteDetailsItemFactory->create();
                            $childDetailsItem = $this->mapper->mapItem(
                                $child,
                                $childDetailsItem,
                                $this->isPriceIncludesTax,
                                $this->isBaseCurrency,
                                $this->totalsProvider,
                                $parentDetailsItem->getCode()
                            );
                            $quoteDetailsItems[] = $childDetailsItem;

                            $extraTaxables = $item->getAssociatedTaxables();
                            if ($extraTaxables && count($extraTaxables) > 0) {
                                $extraTaxableDetailsItems = [];
                                foreach ($extraTaxables as $extraTaxable) {
                                    $extraTaxableDetailsItem = $this->quoteDetailsItemFactory->create();
                                    $extraTaxableDetailsItem = $this->mapper->mapItemExtraTaxable(
                                        $item,
                                        $extraTaxable,
                                        $extraTaxableDetailsItem,
                                        $this->isPriceIncludesTax,
                                        $this->isBaseCurrency
                                    );
                                    $extraTaxableDetailsItems[] = $extraTaxableDetailsItem;
                                }
                                $quoteDetailsItems = array_merge($quoteDetailsItems, $extraTaxableDetailsItems);
                            }
                        }
                    } else {
                        $detailsItem = $this->quoteDetailsItemFactory->create();
                        $detailsItem = $this->mapper->mapItem(
                            $item,
                            $detailsItem,
                            $this->isPriceIncludesTax,
                            $this->isBaseCurrency,
                            $this->totalsProvider
                        );
                        $quoteDetailsItems[] = $detailsItem;

                        $extraTaxables = $item->getAssociatedTaxables();
                        if ($extraTaxables && count($extraTaxables) > 0) {
                            $extraTaxableDetailsItems = [];
                            foreach ($extraTaxables as $extraTaxable) {
                                $extraTaxableDetailsItem = $this->quoteDetailsItemFactory->create();
                                $extraTaxableDetailsItem = $this->mapper->mapItemExtraTaxable(
                                    $item,
                                    $extraTaxable,
                                    $extraTaxableDetailsItem,
                                    $this->isPriceIncludesTax,
                                    $this->isBaseCurrency
                                );
                                $extraTaxableDetailsItems[] = $extraTaxableDetailsItem;
                            }
                            $quoteDetailsItems = array_merge($quoteDetailsItems, $extraTaxableDetailsItems);
                        }
                    }
                }
            }
        }

        return $quoteDetailsItems;
    }

    /**
     * Build shipping quote details
     *
     * @return QuoteDetailsInterface
     */
    private function buildShippingQuoteDetails()
    {
        $detailsItem = $this->buildShippingQuoteDetailsItem();
        return $detailsItem
            ? $this->mapper->mapQuote(
                $this->shippingAssignment,
                [$detailsItem],
                $this->quoteDetailsFactory->create()
            )
            : null;
    }

    /**
     * Build shipping quote details item
     *
     * @return QuoteDetailsItemInterface|null
     */
    private function buildShippingQuoteDetailsItem()
    {
        /** @var Address $address */
        $address = $this->shippingAssignment->getShipping()->getAddress();
        $shippingAmount = $this->totalsProvider->getShippingAmount($address, $this->isBaseCurrency);
        return $shippingAmount > 0
            ? $this->mapper->mapShippingItem(
                $this->quoteDetailsItemFactory->create(),
                $this->dataObjectFactory->create(
                    [
                        'address' => $address,
                        'use_base_currency' => $this->isBaseCurrency,
                        'tax_class' => $this->taxClass,
                        'price_includes_tax' => $this->isPriceIncludesTax
                    ]
                ),
                $this->totalsProvider
            )
            : null;
    }

    /**
     * Check if state is valid for build
     *
     * @return bool
     */
    private function isStateValid()
    {
        if (isset($this->itemType)) {
            if ($this->itemType == 'product') {
                return isset($this->shippingAssignment)
                    && isset($this->isPriceIncludesTax)
                    && isset($this->isBaseCurrency)
                    && isset($this->totalsProvider);
            } else {
                return isset($this->shippingAssignment)
                    && isset($this->isPriceIncludesTax)
                    && isset($this->isBaseCurrency)
                    && isset($this->taxClass)
                    && isset($this->totalsProvider);
            }
        }
        return false;
    }

    /**
     * Reset state
     *
     * @return void
     */
    private function resetState()
    {
        $this->itemType = null;
        $this->shippingAssignment = null;
        $this->isPriceIncludesTax = null;
        $this->isBaseCurrency = null;
        $this->totalsProvider = null;
    }
}
