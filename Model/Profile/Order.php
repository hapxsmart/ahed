<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderExtensionInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order as ProfileOrderResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Order
 * @package Aheadworks\Sarp2\Model\Profile
 */
class Order extends AbstractModel implements ProfileOrderInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ProfileOrderResource::class);
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
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderIncrementId()
    {
        return $this->getData(self::ORDER_INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        return $this->setData(self::ORDER_INCREMENT_ID, $orderIncrementId);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderDate()
    {
        return $this->getData(self::ORDER_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderDate($orderDate)
    {
        return $this->setData(self::ORDER_DATE, $orderDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseGrandTotal()
    {
        return $this->getData(self::BASE_GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseGrandTotal($baseGrandTotal)
    {
        return $this->setData(self::BASE_GRAND_TOTAL, $baseGrandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getGrandTotal()
    {
        return $this->getData(self::GRAND_TOTAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setGrandTotal($grandTotal)
    {
        return $this->setData(self::GRAND_TOTAL, $grandTotal);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getData(self::BASE_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCurrencyCode($baseCurrencyCode)
    {
        return $this->setData(self::BASE_CURRENCY_CODE, $baseCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderCurrencyCode()
    {
        return $this->getData(self::ORDER_CURRENCY_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderCurrencyCode($orderCurrencyCode)
    {
        return $this->setData(self::ORDER_CURRENCY_CODE, $orderCurrencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderStatus()
    {
        return $this->getData(self::ORDER_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderStatus($orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
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
    public function getIsInitial()
    {
        return $this->getData(self::IS_INITIAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitial($isInitial)
    {
        return $this->setData(self::IS_INITIAL, $isInitial);
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ProfileOrderExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
