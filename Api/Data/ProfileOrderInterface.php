<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ProfileOrderInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ProfileOrderInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PROFILE_ID = 'profile_id';
    const ORDER_ID = 'order_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const ORDER_DATE = 'order_date';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const GRAND_TOTAL = 'grand_total';
    const BASE_CURRENCY_CODE = 'base_currency_code';
    const ORDER_CURRENCY_CODE = 'order_currency_code';
    const ORDER_STATUS = 'order_status';
    const IS_INITIAL = 'is_initial';
    /**#@-*/

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
     * Get order ID
     *
     * @return int
     */
    public function getOrderId();

    /**
     * Set order ID
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get order increment ID
     *
     * @return string
     */
    public function getOrderIncrementId();

    /**
     * Set order increment ID
     *
     * @param string $orderIncrementId
     * @return $this
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * Get order date
     *
     * @return string
     */
    public function getOrderDate();

    /**
     * Set order date
     *
     * @param string $orderDate
     * @return $this
     */
    public function setOrderDate($orderDate);

    /**
     * Get grand total in base currency
     *
     * @return float
     */
    public function getBaseGrandTotal();

    /**
     * Set grand total in base currency
     *
     * @param float $baseGrandTotal
     * @return $this
     */
    public function setBaseGrandTotal($baseGrandTotal);

    /**
     * Get grand total in order currency
     *
     * @return float
     */
    public function getGrandTotal();

    /**
     * Set grand total in order currency
     *
     * @param float $grandTotal
     * @return $this
     */
    public function setGrandTotal($grandTotal);

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode();

    /**
     * Set base currency code
     *
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);

    /**
     * Get order currency code
     *
     * @return string
     */
    public function getOrderCurrencyCode();

    /**
     * Set order currency code
     *
     * @param string $orderCurrencyCode
     * @return $this
     */
    public function setOrderCurrencyCode($orderCurrencyCode);

    /**
     * Get order status
     *
     * @return string
     */
    public function getOrderStatus();

    /**
     * Set order status
     *
     * @param string $orderStatus
     * @return $this
     */
    public function setOrderStatus($orderStatus);

    /**
     * Get is order initial
     *
     * @return boolean
     */
    public function getIsInitial();

    /**
     * Set is order initial
     *
     * @param boolean $isInitial
     * @return $this
     */
    public function setIsInitial($isInitial);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\ProfileOrderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\ProfileOrderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\ProfileOrderExtensionInterface $extensionAttributes
    );
}
