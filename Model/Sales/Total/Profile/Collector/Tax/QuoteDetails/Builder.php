<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsChildrenCalculated;
use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;

/**
 * Class Builder
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Tax\QuoteDetails
 */
class Builder
{
    /**
     * @var string
     */
    private $itemType;

    /**
     * @var ProfileInterface
     */
    private $profile;

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
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
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
            $quoteDetails = $this->mapper->mapProfile(
                $quoteDetails,
                $itemDetails,
                $this->profile
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

        $items = $this->profile->getItems();
        if (count($items)) {
            /** @var Item $item */
            foreach ($items as $item) {
                if ($item->hasChildItems() && $this->isChildCalculatedChecker->check($item)) {
                    $parentDetailsItem = $this->quoteDetailsItemFactory->create();
                    $parentDetailsItem = $this->mapper->mapItem(
                        $parentDetailsItem,
                        $this->dataObjectFactory->create(
                            [
                                'item' => $item,
                                'is_price_includes_tax' => $this->isPriceIncludesTax,
                                'is_base_currency' => $this->isBaseCurrency,
                                'totals_provider' => $this->totalsProvider,
                                'store_id' => $this->profile->getStoreId()
                            ]
                        )
                    );
                    $quoteDetailsItems[] = $parentDetailsItem;

                    foreach ($item->getChildItems() as $child) {
                        $childDetailsItem = $this->quoteDetailsItemFactory->create();
                        $childDetailsItem = $this->mapper->mapItem(
                            $childDetailsItem,
                            $this->dataObjectFactory->create(
                                [
                                    'item' => $child,
                                    'is_price_includes_tax' => $this->isPriceIncludesTax,
                                    'is_base_currency' => $this->isBaseCurrency,
                                    'totals_provider' => $this->totalsProvider,
                                    'store_id' => $this->profile->getStoreId(),
                                    'parent_code' => $parentDetailsItem->getCode()
                                ]
                            )
                        );
                        $quoteDetailsItems[] = $childDetailsItem;
                    }
                } elseif (!$item->getParentItem()) {
                    $detailsItem = $this->quoteDetailsItemFactory->create();
                    $detailsItem = $this->mapper->mapItem(
                        $detailsItem,
                        $this->dataObjectFactory->create(
                            [
                                'item' => $item,
                                'is_price_includes_tax' => $this->isPriceIncludesTax,
                                'is_base_currency' => $this->isBaseCurrency,
                                'totals_provider' => $this->totalsProvider,
                                'store_id' => $this->profile->getStoreId()
                            ]
                        )
                    );
                    $quoteDetailsItems[] = $detailsItem;
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
            ? $this->mapper->mapProfile(
                $this->quoteDetailsFactory->create(),
                [$detailsItem],
                $this->profile
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
        $address = $this->profile->getShippingAddress();
        $shippingAmount = $this->totalsProvider->getShippingAmount($address, $this->isBaseCurrency);
        return $shippingAmount > 0
            ? $this->mapper->mapShippingItem(
                $this->quoteDetailsItemFactory->create(),
                $this->dataObjectFactory->create(
                    [
                        'address' => $address,
                        'is_base_currency' => $this->isBaseCurrency,
                        'is_price_includes_tax' => $this->isPriceIncludesTax,
                        'tax_class' => $this->taxClass,
                        'totals_provider' => $this->totalsProvider
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
        if (isset($this->itemType)) {
            if ($this->itemType == 'product') {
                return isset($this->profile)
                    && isset($this->isPriceIncludesTax)
                    && isset($this->isBaseCurrency)
                    && isset($this->totalsProvider);
            } else {
                return isset($this->profile)
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
        $this->profile = null;
        $this->isPriceIncludesTax = null;
        $this->isBaseCurrency = null;
        $this->totalsProvider = null;
    }
}
