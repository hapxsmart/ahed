<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemExtensionInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item as ItemResource;

class Item extends AbstractModel implements ProfileItemInterface
{
    const ONE_OFF_ITEM_OPTION = 'one_off';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProfileItemInterface[]
     */
    private $children = [];

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ItemResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductType()
    {
        return $this->getData(self::PRODUCT_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductType($productType)
    {
        return $this->setData(self::PRODUCT_TYPE, $productType);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductOptions()
    {
        return $this->getData(self::PRODUCT_OPTIONS);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductOptions($productOptions)
    {
        return $this->setData(self::PRODUCT_OPTIONS, $productOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentItem()
    {
        return $this->getData(self::PARENT_ITEM);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentItem($item)
    {
        if ($item) {
            $item->addChild($this);
        }
        return $this->setData(self::PARENT_ITEM, $item);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentItemId()
    {
        return $this->getData(self::PARENT_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentItemId($parentItemId)
    {
        return $this->setData(self::PARENT_ITEM_ID, $parentItemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getReplacementItemId()
    {
        return $this->getData(self::REPLACEMENT_ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setReplacementItemId($itemId)
    {
        return $this->setData(self::REPLACEMENT_ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsVirtual()
    {
        return $this->getData(self::IS_VIRTUAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVirtual($isVirtual)
    {
        return $this->setData(self::IS_VIRTUAL, $isVirtual);
    }

    /**
     * {@inheritdoc}
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsQtyDecimal()
    {
        return $this->getData(self::IS_QTY_DECIMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsQtyDecimal($isQtyDecimal)
    {
        return $this->setData(self::IS_QTY_DECIMAL, $isQtyDecimal);
    }

    /**
     * {@inheritdoc}
     */
    public function getWeight()
    {
        return $this->getData(self::WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setWeight($weight)
    {
        return $this->setData(self::WEIGHT, $weight);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsFreeShipping()
    {
        return $this->getData(self::IS_FREE_SHIPPING);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsFreeShipping($isFreeShipping)
    {
        return $this->setData(self::IS_FREE_SHIPPING, $isFreeShipping);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialFee()
    {
        return $this->getData(self::INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialFee($initialFee)
    {
        return $this->setData(self::INITIAL_FEE, $initialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialFee()
    {
        return $this->getData(self::BASE_INITIAL_FEE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialFee($baseInitialFee)
    {
        return $this->setData(self::BASE_INITIAL_FEE, $baseInitialFee);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPrice()
    {
        return $this->getData(self::INITIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialPrice($initialPrice)
    {
        return $this->setData(self::INITIAL_PRICE, $initialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialPrice()
    {
        return $this->getData(self::BASE_INITIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialPrice($baseInitialPrice)
    {
        return $this->setData(self::BASE_INITIAL_PRICE, $baseInitialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPriceInclTax()
    {
        return $this->getData(self::INITIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialPriceInclTax($initialPriceInclTax)
    {
        return $this->setData(self::INITIAL_PRICE_INCL_TAX, $initialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialPriceInclTax()
    {
        return $this->getData(self::BASE_INITIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialPriceInclTax($baseInitialPriceInclTax)
    {
        return $this->setData(self::BASE_INITIAL_PRICE_INCL_TAX, $baseInitialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialRowTotal()
    {
        return $this->getData(self::INITIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialRowTotal($initialRowTotal)
    {
        return $this->setData(self::INITIAL_ROW_TOTAL, $initialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialRowTotal()
    {
        return $this->getData(self::BASE_INITIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialRowTotal($baseInitialRowTotal)
    {
        return $this->setData(self::BASE_INITIAL_ROW_TOTAL, $baseInitialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialRowTotalInclTax()
    {
        return $this->getData(self::INITIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialRowTotalInclTax($initialRowTotalInclTax)
    {
        return $this->setData(self::INITIAL_ROW_TOTAL_INCL_TAX, $initialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialRowTotalInclTax()
    {
        return $this->getData(self::BASE_INITIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialRowTotalInclTax($baseInitialRowTotalInclTax)
    {
        return $this->setData(self::BASE_INITIAL_ROW_TOTAL_INCL_TAX, $baseInitialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPriceTaxAmount()
    {
        return $this->getData(self::INITIAL_PRICE_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialPriceTaxAmount($initialPriceTaxAmount)
    {
        return $this->setData(self::INITIAL_PRICE_TAX_AMOUNT, $initialPriceTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseInitialPriceTaxAmount()
    {
        return $this->getData(self::BASE_INITIAL_PRICE_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseInitialPriceTaxAmount($baseInitialPriceTaxAmount)
    {
        return $this->setData(self::BASE_INITIAL_PRICE_TAX_AMOUNT, $baseInitialPriceTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitialPriceTaxPercent()
    {
        return $this->getData(self::INITIAL_PRICE_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitialPriceTaxPercent($initialPriceTaxPercent)
    {
        return $this->setData(self::INITIAL_PRICE_TAX_PERCENT, $initialPriceTaxPercent);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPrice()
    {
        return $this->getData(self::TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPrice($trialPrice)
    {
        return $this->setData(self::TRIAL_PRICE, $trialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialPrice()
    {
        return $this->getData(self::BASE_TRIAL_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialPrice($baseTrialPrice)
    {
        return $this->setData(self::BASE_TRIAL_PRICE, $baseTrialPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialPriceInclTax()
    {
        return $this->getData(self::TRIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialPriceInclTax($trialPriceInclTax)
    {
        return $this->setData(self::TRIAL_PRICE_INCL_TAX, $trialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialPriceInclTax()
    {
        return $this->getData(self::BASE_TRIAL_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialPriceInclTax($baseTrialPriceInclTax)
    {
        return $this->setData(self::BASE_TRIAL_PRICE_INCL_TAX, $baseTrialPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialRowTotal()
    {
        return $this->getData(self::TRIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialRowTotal($trialRowTotal)
    {
        return $this->setData(self::TRIAL_ROW_TOTAL, $trialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialRowTotal()
    {
        return $this->getData(self::BASE_TRIAL_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialRowTotal($baseTrialRowTotal)
    {
        return $this->setData(self::BASE_TRIAL_ROW_TOTAL, $baseTrialRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialRowTotalInclTax()
    {
        return $this->getData(self::TRIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialRowTotalInclTax($trialRowTotalInclTax)
    {
        return $this->setData(self::TRIAL_ROW_TOTAL_INCL_TAX, $trialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialRowTotalInclTax()
    {
        return $this->getData(self::BASE_TRIAL_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialRowTotalInclTax($baseTrialRowTotalInclTax)
    {
        return $this->setData(self::BASE_TRIAL_ROW_TOTAL_INCL_TAX, $baseTrialRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxAmount()
    {
        return $this->getData(self::TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTaxAmount($trialTaxAmount)
    {
        return $this->setData(self::TRIAL_TAX_AMOUNT, $trialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTrialTaxPercent()
    {
        return $this->getData(self::TRIAL_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTrialTaxPercent($trialTaxPercent)
    {
        return $this->setData(self::TRIAL_TAX_PERCENT, $trialTaxPercent);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTrialTaxAmount()
    {
        return $this->getData(self::BASE_TRIAL_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount)
    {
        return $this->setData(self::BASE_TRIAL_TAX_AMOUNT, $baseTrialTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPrice()
    {
        return $this->getData(self::REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPrice($regularPrice)
    {
        return $this->setData(self::REGULAR_PRICE, $regularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularPrice()
    {
        return $this->getData(self::BASE_REGULAR_PRICE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularPrice($baseRegularPrice)
    {
        return $this->setData(self::BASE_REGULAR_PRICE, $baseRegularPrice);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularPriceInclTax()
    {
        return $this->getData(self::REGULAR_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularPriceInclTax($regularPriceInclTax)
    {
        return $this->setData(self::REGULAR_PRICE_INCL_TAX, $regularPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularPriceInclTax()
    {
        return $this->getData(self::BASE_REGULAR_PRICE_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularPriceInclTax($baseRegularPriceInclTax)
    {
        return $this->setData(self::BASE_REGULAR_PRICE_INCL_TAX, $baseRegularPriceInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularRowTotal()
    {
        return $this->getData(self::REGULAR_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularRowTotal($regularRowTotal)
    {
        return $this->setData(self::REGULAR_ROW_TOTAL, $regularRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularRowTotal()
    {
        return $this->getData(self::BASE_REGULAR_ROW_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularRowTotal($baseRegularRowTotal)
    {
        return $this->setData(self::BASE_REGULAR_ROW_TOTAL, $baseRegularRowTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularRowTotalInclTax()
    {
        return $this->getData(self::REGULAR_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularRowTotalInclTax($regularRowTotalInclTax)
    {
        return $this->setData(self::REGULAR_ROW_TOTAL_INCL_TAX, $regularRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularRowTotalInclTax()
    {
        return $this->getData(self::BASE_REGULAR_ROW_TOTAL_INCL_TAX);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularRowTotalInclTax($baseRegularRowTotalInclTax)
    {
        return $this->setData(self::BASE_REGULAR_ROW_TOTAL_INCL_TAX, $baseRegularRowTotalInclTax);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularTaxAmount()
    {
        return $this->getData(self::REGULAR_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularTaxAmount($regularTaxAmount)
    {
        return $this->setData(self::REGULAR_TAX_AMOUNT, $regularTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseRegularTaxAmount()
    {
        return $this->getData(self::BASE_REGULAR_TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRegularTaxAmount($baseRegularTaxAmount)
    {
        return $this->setData(self::BASE_REGULAR_TAX_AMOUNT, $baseRegularTaxAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRegularTaxPercent()
    {
        return $this->getData(self::REGULAR_TAX_PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRegularTaxPercent($regularTaxPercent)
    {
        return $this->setData(self::REGULAR_TAX_PERCENT, $regularTaxPercent);
    }

    /**
     * {@inheritdoc}
     */
    public function getRowWeight()
    {
        return $this->getData(self::ROW_WEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowWeight($rowWeight)
    {
        return $this->setData(self::ROW_WEIGHT, $rowWeight);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ProfileItemExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * Add child item
     *
     * @param ProfileItemInterface $child
     * @return $this
     */
    public function addChild($child)
    {
        $this->children[] = $child;
        return $this;
    }

    /**
     * Get children
     *
     * @return ProfileItemInterface[]
     */
    public function getChildItems()
    {
        return $this->children;
    }

    /**
     * One more method used by Magento to get child items
     *
     * @return ProfileItemInterface[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Checking if children items are calculated or parent item when we have parent item and its children
     *
     * @return bool
     */
    public function isChildrenCalculated()
    {
        $calculate = $this->getParentItem()
            ? $this->getParentItem()->getProduct()->getPriceType()
            : $this->getProduct()->getPriceType();

        return null !== $calculate && (int)$calculate === AbstractType::CALCULATE_CHILD;
    }

    /**
     * Check if item has children
     *
     * @return bool
     */
    public function hasChildItems()
    {
        return count($this->children) > 0;
    }

    /**
     * Retrieve product
     *
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->productRepository->getById($this->getProductId());
    }
}
