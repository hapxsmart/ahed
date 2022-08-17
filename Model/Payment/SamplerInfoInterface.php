<?php
namespace Aheadworks\Sarp2\Model\Payment;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface SamplerInfoInterface
 * @package Aheadworks\Sarp2\Model\Payment
 */
interface SamplerInfoInterface extends InfoInterface
{
    /**#@+
     * Payment sampler entity statuses
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PLACED = 'placed';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_FAILED = 'failed';
    /**#@-*/

    /**
     * Get payment sampler Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set payment sampler Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getMethod();

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method);

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get last transaction id
     *
     * @return string
     */
    public function getLastTransactionId();

    /**
     * Set last transaction id
     *
     * @param string $lastTransactionId
     * @return $this
     */
    public function setLastTransactionId($lastTransactionId);

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get customer Id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer Id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId);

    /**
     * Get quote Id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set quote Id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get placed amount
     *
     * @return float
     */
    public function getAmountPlaced();

    /**
     * Set placed amount
     *
     * @param float $amountPlaced
     * @return $this
     */
    public function setAmountPlaced($amountPlaced);

    /**
     * Get reverted amount
     *
     * @return float
     */
    public function getAmountReverted();

    /**
     * Set reverted amount
     *
     * @param float $amountReverted
     * @return $this
     */
    public function setAmountReverted($amountReverted);

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Get remote IP address
     *
     * @return string
     */
    public function getRemoteIp();

    /**
     * Set remote IP address
     *
     * @param string $remoteIp
     * @return $this
     */
    public function setRemoteIp($remoteIp);

    /**
     * Get profile id
     *
     * @return int
     */
    public function getProfileId();

    /**
     * Set profile id
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * Get profile
     *
     * @return ProfileInterface
     */
    public function getProfile();

    /**
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile);

    /**
     * Get order
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Set order
     *
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder($order);

    /**
     * Set additional information
     *
     * @param string $key
     * @param string|null $value
     * @return mixed
     */
    public function setAdditionalInformation($key, $value = null);

    /**
     * Check if exists additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     */
    public function hasAdditionalInformation($key = null);

    /**
     * Unset additional information value specified by key
     *
     * @param string|null $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null);

    /**
     * Get additional information value specified by key
     *
     * @param string|null $key
     * @return mixed
     */
    public function getAdditionalInformation($key = null);
}
