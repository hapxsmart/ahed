<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfileItemInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileItemInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ITEM_ID = 'item_id';
    const PROFILE_ID = 'profile_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const PRODUCT_ID = 'product_id';
    const PRODUCT_TYPE = 'product_type';
    const PRODUCT_OPTIONS = 'product_options';
    const STORE_ID = 'store_id';
    const PARENT_ITEM = 'parent_item';
    const PARENT_ITEM_ID = 'parent_item_id';
    const REPLACEMENT_ITEM_ID = 'replacement_item_id';
    const IS_VIRTUAL = 'is_virtual';
    const SKU = 'sku';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const IS_QTY_DECIMAL = 'is_qty_decimal';
    const WEIGHT = 'weight';
    const QTY = 'qty';
    const IS_FREE_SHIPPING = 'is_free_shipping';
    const INITIAL_FEE = 'initial_fee';
    const BASE_INITIAL_FEE = 'base_initial_fee';
    const INITIAL_PRICE = 'initial_price';
    const BASE_INITIAL_PRICE = 'base_initial_price';
    const INITIAL_PRICE_INCL_TAX = 'initial_price_incl_tax';
    const BASE_INITIAL_PRICE_INCL_TAX = 'base_initial_price_incl_tax';
    const INITIAL_ROW_TOTAL = 'initial_row_total';
    const BASE_INITIAL_ROW_TOTAL = 'base_initial_row_total';
    const INITIAL_ROW_TOTAL_INCL_TAX = 'initial_row_total_incl_tax';
    const BASE_INITIAL_ROW_TOTAL_INCL_TAX = 'base_initial_row_total_incl_tax';
    const INITIAL_PRICE_TAX_AMOUNT = 'initial_price_tax_amount';
    const BASE_INITIAL_PRICE_TAX_AMOUNT = 'base_initial_price_tax_amount';
    const INITIAL_PRICE_TAX_PERCENT = 'initial_price_tax_percent';
    const TRIAL_PRICE = 'trial_price';
    const BASE_TRIAL_PRICE = 'base_trial_price';
    const TRIAL_PRICE_INCL_TAX = 'trial_price_incl_tax';
    const BASE_TRIAL_PRICE_INCL_TAX = 'base_trial_price_incl_tax';
    const TRIAL_ROW_TOTAL = 'trial_row_total';
    const BASE_TRIAL_ROW_TOTAL = 'base_trial_row_total';
    const TRIAL_ROW_TOTAL_INCL_TAX = 'trial_row_total_incl_tax';
    const BASE_TRIAL_ROW_TOTAL_INCL_TAX = 'base_trial_row_total_incl_tax';
    const TRIAL_TAX_AMOUNT = 'trial_tax_amount';
    const TRIAL_TAX_PERCENT = 'trial_tax_percent';
    const BASE_TRIAL_TAX_AMOUNT = 'base_trial_tax_amount';
    const REGULAR_PRICE = 'regular_price';
    const BASE_REGULAR_PRICE = 'base_regular_price';
    const REGULAR_PRICE_INCL_TAX = 'regular_price_incl_tax';
    const BASE_REGULAR_PRICE_INCL_TAX = 'base_regular_price_incl_tax';
    const REGULAR_ROW_TOTAL = 'regular_row_total';
    const BASE_REGULAR_ROW_TOTAL = 'base_regular_row_total';
    const REGULAR_ROW_TOTAL_INCL_TAX = 'regular_row_total_incl_tax';
    const BASE_REGULAR_ROW_TOTAL_INCL_TAX = 'base_regular_row_total_incl_tax';
    const REGULAR_TAX_AMOUNT = 'regular_tax_amount';
    const REGULAR_TAX_PERCENT = 'regular_tax_percent';
    const BASE_REGULAR_TAX_AMOUNT = 'base_regular_tax_amount';
    const ROW_WEIGHT = 'row_weight';
    /**#@-*/

    /**
     * Get profile item ID
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * Set profile item ID
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Get profile ID
     *
     * @return int
     */
    public function getProfileId();

    /**
     * Set profile ID
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get product ID
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product ID
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get product type
     *
     * @return string
     */
    public function getProductType();

    /**
     * Set product type
     *
     * @param string $productType
     * @return $this
     */
    public function setProductType($productType);

    /**
     * Get product options
     *
     * @return \Magento\Catalog\Api\Data\ProductOptionInterface|null
     */
    public function getProductOptions();

    /**
     * Set product options
     *
     * @param \Magento\Catalog\Api\Data\ProductOptionInterface $productOptions
     * @return $this
     */
    public function setProductOptions($productOptions);

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get parent item
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemInterface|null
     */
    public function getParentItem();

    /**
     * Set parent item
     *
     * @param ProfileItemInterface $item
     * @return $this
     */
    public function setParentItem($item);

    /**
     * Get parent item ID
     *
     * @return int|null
     */
    public function getParentItemId();

    /**
     * Set parent item ID
     *
     * @param int|null $parentItemId
     * @return $this
     */
    public function setParentItemId($parentItemId);

    /**
     * Get replacement item ID
     *
     * @return int|null
     */
    public function getReplacementItemId();

    /**
     * Set replacement item ID
     *
     * @param int|null $itemId
     * @return $this
     */
    public function setReplacementItemId($itemId);

    /**
     * Check if item virtual
     *
     * @return bool
     */
    public function getIsVirtual();

    /**
     * Set virtual flag
     *
     * @param bool $isVirtual
     * @return $this
     */
    public function setIsVirtual($isVirtual);

    /**
     * Get product SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set product SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get description for profile item
     *
     * @return string
     */
    public function getDescription();

    /**
     * Set description for profile item
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Check if quantity decimal
     *
     * @return bool
     */
    public function getIsQtyDecimal();

    /**
     * Set quantity is decimal flag
     *
     * @param bool $isQtyDecimal
     * @return $this
     */
    public function setIsQtyDecimal($isQtyDecimal);

    /**
     * Get profile item weight
     *
     * @return float|null
     */
    public function getWeight();

    /**
     * Set profile item weight
     *
     * @param float $weight
     * @return $this
     */
    public function setWeight($weight);

    /**
     * Get the product quantity
     *
     * @return float
     */
    public function getQty();

    /**
     * Set the product quantity
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get free shipping flag
     *
     * @return bool
     */
    public function getIsFreeShipping();

    /**
     * Set free shipping flag
     *
     * @param bool $isFreeShipping
     * @return $this
     */
    public function setIsFreeShipping($isFreeShipping);

    /**
     * Get initial fee in profile currency
     *
     * @return float
     */
    public function getInitialFee();

    /**
     * Set initial fee in profile currency
     *
     * @param float $initialFee
     * @return $this
     */
    public function setInitialFee($initialFee);

    /**
     * Get base initial fee
     *
     * @return float
     */
    public function getBaseInitialFee();

    /**
     * Set base initial fee
     *
     * @param float $baseInitialFee
     * @return $this
     */
    public function setBaseInitialFee($baseInitialFee);

    /**
     * Get initial price in profile currency
     *
     * @return float
     */
    public function getInitialPrice();

    /**
     * Set initial price in profile currency
     *
     * @param float $initialPrice
     * @return $this
     */
    public function setInitialPrice($initialPrice);

    /**
     * Get initial price in base currency
     *
     * @return float
     */
    public function getBaseInitialPrice();

    /**
     * Set initial price in base currency
     *
     * @param float $baseInitialPrice
     * @return $this
     */
    public function setBaseInitialPrice($baseInitialPrice);

    /**
     * Get initial price including tax in profile currency
     *
     * @return float
     */
    public function getInitialPriceInclTax();

    /**
     * Set initial price including tax in profile currency
     *
     * @param float $initialPriceInclTax
     * @return $this
     */
    public function setInitialPriceInclTax($initialPriceInclTax);

    /**
     * Get initial price including tax in base currency
     *
     * @return float
     */
    public function getBaseInitialPriceInclTax();

    /**
     * Set initial price including tax in base currency
     *
     * @param float $baseInitialPriceInclTax
     * @return $this
     */
    public function setBaseInitialPriceInclTax($baseInitialPriceInclTax);

    /**
     * Get initial row total in profile currency
     *
     * @return float
     */
    public function getInitialRowTotal();

    /**
     * Set initial row total in profile currency
     *
     * @param float $initialRowTotal
     * @return $this
     */
    public function setInitialRowTotal($initialRowTotal);

    /**
     * Get initial row total in base currency
     *
     * @return float
     */
    public function getBaseInitialRowTotal();

    /**
     * Set initial row total in base currency
     *
     * @param float $baseInitialRowTotal
     * @return $this
     */
    public function setBaseInitialRowTotal($baseInitialRowTotal);

    /**
     * Get initial row total including tax in profile currency
     *
     * @return float
     */
    public function getInitialRowTotalInclTax();

    /**
     * Set initial row total including tax in profile currency
     *
     * @param float $initialRowTotalInclTax
     * @return $this
     */
    public function setInitialRowTotalInclTax($initialRowTotalInclTax);

    /**
     * Get initial row total including tax in base currency
     *
     * @return float
     */
    public function getBaseInitialRowTotalInclTax();

    /**
     * Set initial row total including tax in base currency
     *
     * @param float $baseInitialRowTotalInclTax
     * @return $this
     */
    public function setBaseInitialRowTotalInclTax($baseInitialRowTotalInclTax);

    /**
     * Get initial price tax amount in profile currency
     *
     * @return float
     */
    public function getInitialPriceTaxAmount();

    /**
     * Set initial price tax amount in profile currency
     *
     * @param float $initialPriceTaxAmount
     * @return $this
     */
    public function setInitialPriceTaxAmount($initialPriceTaxAmount);

    /**
     * Get initial price tax amount in base currency
     *
     * @return float
     */
    public function getBaseInitialPriceTaxAmount();

    /**
     * Set initial price tax amount in base currency
     *
     * @param float $baseInitialPriceTaxAmount
     * @return $this
     */
    public function setBaseInitialPriceTaxAmount($baseInitialPriceTaxAmount);

    /**
     * Get initial price tax percent
     *
     * @return float
     */
    public function getInitialPriceTaxPercent();

    /**
     * Set initial price tax percent
     *
     * @param float $initialPriceTaxPercent
     * @return $this
     */
    public function setInitialPriceTaxPercent($initialPriceTaxPercent);

    /**
     * Get trial price in profile currency
     *
     * @return float
     */
    public function getTrialPrice();

    /**
     * Set trial price in profile currency
     *
     * @param float $trialPrice
     * @return $this
     */
    public function setTrialPrice($trialPrice);

    /**
     * Get trial price in base currency
     *
     * @return float
     */
    public function getBaseTrialPrice();

    /**
     * Set trial price in base currency
     *
     * @param float $baseTrialPrice
     * @return $this
     */
    public function setBaseTrialPrice($baseTrialPrice);

    /**
     * Get trial price including tax in profile currency
     *
     * @return float
     */
    public function getTrialPriceInclTax();

    /**
     * Set trial price including tax in profile currency
     *
     * @param float $trialPriceInclTax
     * @return $this
     */
    public function setTrialPriceInclTax($trialPriceInclTax);

    /**
     * Get trial price including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialPriceInclTax();

    /**
     * Set trial price including tax in base currency
     *
     * @param float $baseTrialPriceInclTax
     * @return $this
     */
    public function setBaseTrialPriceInclTax($baseTrialPriceInclTax);

    /**
     * Get trial row total in profile currency
     *
     * @return float
     */
    public function getTrialRowTotal();

    /**
     * Set trial row total in profile currency
     *
     * @param float $trialRowTotal
     * @return $this
     */
    public function setTrialRowTotal($trialRowTotal);

    /**
     * Get trial row total in base currency
     *
     * @return float
     */
    public function getBaseTrialRowTotal();

    /**
     * Set trial row total in base currency
     *
     * @param float $baseTrialRowTotal
     * @return $this
     */
    public function setBaseTrialRowTotal($baseTrialRowTotal);

    /**
     * Get trial row total including tax in profile currency
     *
     * @return float
     */
    public function getTrialRowTotalInclTax();

    /**
     * Set trial row total including tax in profile currency
     *
     * @param float $trialRowTotalInclTax
     * @return $this
     */
    public function setTrialRowTotalInclTax($trialRowTotalInclTax);

    /**
     * Get trial row total including tax in base currency
     *
     * @return float
     */
    public function getBaseTrialRowTotalInclTax();

    /**
     * Set trial row total including tax in base currency
     *
     * @param float $baseTrialRowTotalInclTax
     * @return $this
     */
    public function setBaseTrialRowTotalInclTax($baseTrialRowTotalInclTax);

    /**
     * Get trial tax amount in profile currency
     *
     * @return float
     */
    public function getTrialTaxAmount();

    /**
     * Get trial tax percent
     *
     * @return float
     */
    public function getTrialTaxPercent();

    /**
     * Set trial tax percent
     *
     * @param float $trialTaxPercent
     * @return $this
     */
    public function setTrialTaxPercent($trialTaxPercent);

    /**
     * Set trial tax amount in profile currency
     *
     * @param float $trialTaxAmount
     * @return $this
     */
    public function setTrialTaxAmount($trialTaxAmount);

    /**
     * Get trial tax amount in base currency
     *
     * @return float
     */
    public function getBaseTrialTaxAmount();

    /**
     * Set trial tax amount in base currency
     *
     * @param float $baseTrialTaxAmount
     * @return $this
     */
    public function setBaseTrialTaxAmount($baseTrialTaxAmount);

    /**
     * Get regular price in profile currency
     *
     * @return float
     */
    public function getRegularPrice();

    /**
     * Set regular price in profile currency
     *
     * @param float $regularPrice
     * @return $this
     */
    public function setRegularPrice($regularPrice);

    /**
     * Get regular price in base currency
     *
     * @return float
     */
    public function getBaseRegularPrice();

    /**
     * Set regular price in base currency
     *
     * @param float $baseRegularPrice
     * @return $this
     */
    public function setBaseRegularPrice($baseRegularPrice);

    /**
     * Get regular price including tax in profile currency
     *
     * @return float
     */
    public function getRegularPriceInclTax();

    /**
     * Set regular price including tax in profile currency
     *
     * @param float $regularPriceInclTax
     * @return $this
     */
    public function setRegularPriceInclTax($regularPriceInclTax);

    /**
     * Get regular price including tax in base currency
     *
     * @return float
     */
    public function getBaseRegularPriceInclTax();

    /**
     * Set regular price including tax in base currency
     *
     * @param float $baseRegularPriceInclTax
     * @return $this
     */
    public function setBaseRegularPriceInclTax($baseRegularPriceInclTax);

    /**
     * Get regular row total in profile currency
     *
     * @return float
     */
    public function getRegularRowTotal();

    /**
     * Set regular row total in profile currency
     *
     * @param float $regularRowTotal
     * @return $this
     */
    public function setRegularRowTotal($regularRowTotal);

    /**
     * Get regular row total in base currency
     *
     * @return float
     */
    public function getBaseRegularRowTotal();

    /**
     * Set regular row total in base currency
     *
     * @param float $baseRegularRowTotal
     * @return $this
     */
    public function setBaseRegularRowTotal($baseRegularRowTotal);

    /**
     * Get regular row total including tax in profile currency
     *
     * @return float
     */
    public function getRegularRowTotalInclTax();

    /**
     * Set regular row total including tax in profile currency
     *
     * @param float $regularRowTotalInclTax
     * @return $this
     */
    public function setRegularRowTotalInclTax($regularRowTotalInclTax);

    /**
     * Get regular row total including tax in base currency
     *
     * @return float
     */
    public function getBaseRegularRowTotalInclTax();

    /**
     * Set regular row total including tax in base currency
     *
     * @param float $baseRegularRowTotalInclTax
     * @return $this
     */
    public function setBaseRegularRowTotalInclTax($baseRegularRowTotalInclTax);

    /**
     * Get regular tax amount in profile currency
     *
     * @return float
     */
    public function getRegularTaxAmount();

    /**
     * Set regular tax amount in profile currency
     *
     * @param float $regularTaxAmount
     * @return $this
     */
    public function setRegularTaxAmount($regularTaxAmount);

    /**
     * Get regular tax amount in base currency
     *
     * @return float
     */
    public function getBaseRegularTaxAmount();

    /**
     * Set regular tax amount in base currency
     *
     * @param float $baseRegularTaxAmount
     * @return $this
     */
    public function setBaseRegularTaxAmount($baseRegularTaxAmount);

    /**
     * Get regular tax percent
     *
     * @return float
     */
    public function getRegularTaxPercent();

    /**
     * Set regular tax percent
     *
     * @param float $regularTaxPercent
     * @return $this
     */
    public function setRegularTaxPercent($regularTaxPercent);

    /**
     * Get row weight
     *
     * @return float
     */
    public function getRowWeight();

    /**
     * Set row weight
     *
     * @param float $rowWeight
     * @return $this
     */
    public function setRowWeight($rowWeight);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\ProfileItemExtensionInterface $extensionAttributes
    );
}
