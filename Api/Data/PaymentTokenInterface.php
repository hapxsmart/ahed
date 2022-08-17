<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface PaymentTokenInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface PaymentTokenInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const TOKEN_ID = 'token_id';
    const PAYMENT_METHOD = 'payment_method';
    const TYPE = 'type';
    const TOKEN_VALUE = 'token_value';
    const CREATED_AT = 'created_at';
    const EXPIRES_AT = 'expires_at';
    const IS_ACTIVE = 'is_active';
    const DETAILS = 'details';
    /**#@-*/

    /**
     * Get token ID
     *
     * @return int|null
     */
    public function getTokenId();

    /**
     * Set token ID
     *
     * @param int $tokenId
     * @return $this
     */
    public function setTokenId($tokenId);

    /**
     * Get payment method code
     *
     * @return string
     */
    public function getPaymentMethod();

    /**
     * Set payment method code
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);

    /**
     * Get token type
     *
     * @return string
     */
    public function getType();

    /**
     * Set token type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get token value
     *
     * @return string
     */
    public function getTokenValue();

    /**
     * Set token value
     *
     * @param string $tokenValue
     * @return $this
     */
    public function setTokenValue($tokenValue);

    /**
     * Get creation time
     *
     * @return string
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
     * Get expiration time
     *
     * @return string|null
     */
    public function getExpiresAt();

    /**
     * Set expiration time
     *
     * @param string $expiresAt
     * @return $this
     */
    public function setExpiresAt($expiresAt);

    /**
     * Check if token active
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Set is token active flag
     *
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * Getter for entire details value or one of its element by key
     *
     * @param string|null $key
     * @return string[]|string
     */
    public function getDetails($key = null);

    /**
     * Set details
     *
     * @param string|string[] $key
     * @param string|null $value
     * @return $this
     */
    public function setDetails($key, $value = null);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\PaymentTokenExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\PaymentTokenExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\PaymentTokenExtensionInterface $extensionAttributes
    );
}
