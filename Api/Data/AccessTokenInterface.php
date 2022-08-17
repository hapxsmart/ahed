<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface AccessTokenInterface
 *
 * @package Aheadworks\Sarp2\Api\Data
 */
interface AccessTokenInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const TOKEN_VALUE = 'token_value';
    const PROFILE_ID = 'profile_id';
    const CREATED_AT = 'created_at';
    const EXPIRES_AT = 'expires_at';
    const ALLOWED_RESOURCE = 'allowed_resource';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

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
     * Get profile id
     *
     * @return string
     */
    public function getProfileId();

    /**
     * Set profile id
     *
     * @param string $profileId
     * @return $this
     */
    public function setProfileId($profileId);

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
     * Get allowed resource
     *
     * @return string|null
     */
    public function getAllowedResource();

    /**
     * Set allowed resource
     *
     * @param string $allowResource
     * @return $this
     */
    public function setAllowedResource($allowResource);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\AccessTokenExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\AccessTokenExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\AccessTokenExtensionInterface $extensionAttributes
    );
}
