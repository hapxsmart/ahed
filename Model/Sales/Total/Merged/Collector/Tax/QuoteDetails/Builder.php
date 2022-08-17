<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Engine\Profile\PaymentInfoInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsChildrenCalculated;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;

/**
 * Class Builder
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged\Collector\Tax\QuoteDetails
 */
class Builder
{
    /**
     * @var string
     */
    private $itemType;

    /**
     * @var bool
     */
    private $isBaseCurrency;

    /**
     * @var bool
     */
    private $isPriceIncludesTax;

    /**
     * @var PaymentInfoInterface[]
     */
    private $paymentsInfo;

    /**
     * @var array
     */
    private $itemPairs;

    /**
     * @var int
     */
    private $taxClass;

    /**
     * @var float
     */
    private $shippingAmount;

    /**
     * @var int
     */
    private $storeId;

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
     * @var IsChildrenCalculated
     */
    private $isChildCalculatedChecker;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param QuoteDetailsInterfaceFactory $quoteDetailsFactory
     * @param QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory
     * @param Mapper $mapper
     * @param IsChildrenCalculated $isChildCalculatedChecker
     * @param Factory $dataObjectFactory
     */
    public function __construct(
        QuoteDetailsInterfaceFactory $quoteDetailsFactory,
        QuoteDetailsItemInterfaceFactory $quoteDetailsItemFactory,
        Mapper $mapper,
        IsChildrenCalculated $isChildCalculatedChecker,
        Factory $dataObjectFactory
    ) {
        $this->quoteDetailsFactory = $quoteDetailsFactory;
        $this->quoteDetailsItemFactory = $quoteDetailsItemFactory;
        $this->mapper = $mapper;
        $this->isChildCalculatedChecker = $isChildCalculatedChecker;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Set items type
     *
     * @param string $itemType
     * @return $this
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
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
     * Set payments info
     *
     * @param PaymentInfoInterface[] $paymentsInfo
     * @return $this
     */
    public function setPaymentsInfo($paymentsInfo)
    {
        $this->paymentsInfo = $paymentsInfo;
        return $this;
    }

    /**
     * Set item pairs
     *
     * @param array $itemPairs
     * @return $this
     */
    public function setItemPairs($itemPairs)
    {
        $this->itemPairs = $itemPairs;
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
     * Set shipping amount
     *
     * @param float $shippingAmount
     * @return $this
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
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
            $quoteDetails = $this->mapper->mapPaymentsInfo(
                $quoteDetails,
                $itemDetails,
                $this->paymentsInfo
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

        if (count($this->itemPairs)) {
            foreach ($this->itemPairs as $pair) {
                /** @var ProfileItemInterface|Item $profileItem */
                $profileItem = $pair[0]->getItem();
                $paymentPeriod = $pair[0]->getPaymentPeriod();

                if (!$profileItem->getParentItemId()) {
                    if ($profileItem->hasChildItems() && $this->isChildCalculatedChecker->check($profileItem)) {
                        $parentDetailsItem = $this->quoteDetailsItemFactory->create();
                        $parentDetailsItem = $this->mapper->mapItem(
                            $parentDetailsItem,
                            $this->dataObjectFactory->create(
                                [
                                    'item' => $profileItem,
                                    'payment_period' => $paymentPeriod,
                                    'is_price_includes_tax' => $this->isPriceIncludesTax,
                                    'is_base_currency' => $this->isBaseCurrency,
                                    'store_id' => $this->storeId
                                ]
                            )
                        );
                        $quoteDetailsItems[] = $parentDetailsItem;

                        foreach ($profileItem->getChildItems() as $child) {
                            $childDetailsItem = $this->quoteDetailsItemFactory->create();
                            $childDetailsItem = $this->mapper->mapItem(
                                $childDetailsItem,
                                $this->dataObjectFactory->create(
                                    [
                                        'item' => $child,
                                        'payment_period' => $paymentPeriod,
                                        'is_price_includes_tax' => $this->isPriceIncludesTax,
                                        'is_base_currency' => $this->isBaseCurrency,
                                        'store_id' => $this->storeId,
                                        'parent_code' => $parentDetailsItem->getCode()
                                    ]
                                )
                            );
                            $quoteDetailsItems[] = $childDetailsItem;
                        }
                    } else {
                        $detailsItem = $this->quoteDetailsItemFactory->create();
                        $detailsItem = $this->mapper->mapItem(
                            $detailsItem,
                            $this->dataObjectFactory->create(
                                [
                                    'item' => $profileItem,
                                    'payment_period' => $paymentPeriod,
                                    'is_price_includes_tax' => $this->isPriceIncludesTax,
                                    'is_base_currency' => $this->isBaseCurrency,
                                    'store_id' => $this->storeId
                                ]
                            )
                        );
                        $quoteDetailsItems[] = $detailsItem;
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
            ? $this->mapper->mapPaymentsInfo(
                $this->quoteDetailsFactory->create(),
                [$detailsItem],
                $this->paymentsInfo
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
        return $this->shippingAmount > 0
            ? $this->mapper->mapShippingItem(
                $this->quoteDetailsItemFactory->create(),
                $this->dataObjectFactory->create(
                    [
                        'shipping_amount' => $this->shippingAmount,
                        'is_base_currency' => $this->isBaseCurrency,
                        'is_price_includes_tax' => $this->isPriceIncludesTax,
                        'tax_class' => $this->taxClass
                    ]
                )
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
        if (isset($this->itemType) && isset($this->storeId)) {
            if ($this->itemType == 'product') {
                return isset($this->paymentsInfo)
                    && isset($this->itemPairs)
                    && isset($this->isPriceIncludesTax)
                    && isset($this->isBaseCurrency);
            } else {
                return isset($this->paymentsInfo)
                    && isset($this->itemPairs)
                    && isset($this->isPriceIncludesTax)
                    && isset($this->isBaseCurrency)
                    && isset($this->taxClass)
                    && isset($this->shippingAmount);
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
        $this->isPriceIncludesTax = null;
        $this->isBaseCurrency = null;
        $this->paymentsInfo = null;
        $this->itemPairs = null;
        $this->taxClass = null;
        $this->storeId = null;
        $this->shippingAmount = null;
    }
}
