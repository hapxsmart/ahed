<?php
namespace Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address;

use Aheadworks\Sarp2\Api\Data\QuoteAddressItemInterface;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;

/**
 * Class Item
 * @package Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address
 */
class Item extends AddressItem implements QuoteAddressItemInterface
{
    /**
     * {@inheritdoc}
     */
    public function getQuote()
    {
        return $this->getData('quote');
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->getData('address');
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->getData('product');
    }

    /**
     * {@inheritdoc}
     */
    public function getParentItem()
    {
        return $this->getData('parent_item');
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->getData('children');
    }

    /**
     * {@inheritdoc}
     */
    public function addChild($child)
    {
        parent::addChild($child);
        $childrenList = $this->getData('children');
        $actualChildrenList = (empty($childrenList)) ? [] : $childrenList;
        $actualChildrenList[] = $child;
        $this->setData('children', $actualChildrenList);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->getData('store');
    }

    /**
     * {@inheritdoc}
     */
    public function checkData()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getQty()
    {
        return $this->getData('qty');
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalQty()
    {
        return $this->getData('total_qty');
    }

    /**
     * {@inheritdoc}
     */
    public function calcRowTotal()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculationPrice()
    {
        return $this->getData('calculation_price');
    }

    /**
     * {@inheritdoc}
     */
    public function getCalculationPriceOriginal()
    {
        return $this->getData('calculation_price_original');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCalculationPrice()
    {
        return $this->getData('base_calculation_price');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCalculationPriceOriginal()
    {
        return $this->getData('base_calculation_price_original');
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalPrice()
    {
        return $this->getData('original_price');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseOriginalPrice()
    {
        return $this->getData('base_original_price');
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->getData('price');
    }

    /**
     * {@inheritdoc}
     */
    public function getConvertedPrice()
    {
        return $this->getData('converted_price');
    }

    /**
     * {@inheritdoc}
     */
    public function isChildrenCalculated()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isShipSeparately()
    {
        return $this->getData('is_ship_separately');
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalDiscountAmount()
    {
        return $this->getData('total_discount_amount');
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
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\QuoteAddressItemExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
