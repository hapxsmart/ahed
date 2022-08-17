<?php
namespace Aheadworks\Sarp2\Model\Access;

use Aheadworks\Sarp2\Api\Data\AccessTokenExtensionInterface;
use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Access\Token as TokenResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Token
 *
 * @package Aheadworks\Sarp2\Model\Access
 */
class Token extends AbstractModel implements AccessTokenInterface
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(TokenResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenValue()
    {
        return $this->getData(self::TOKEN_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTokenValue($tokenValue)
    {
        return $this->setData(self::TOKEN_VALUE, $tokenValue);
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
    public function getExpiresAt()
    {
        return $this->getData(self::EXPIRES_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresAt($expiresAt)
    {
        return $this->setData(self::EXPIRES_AT, $expiresAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedResource()
    {
        return $this->getData(self::ALLOWED_RESOURCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowedResource($allowResource)
    {
        return $this->setData(self::ALLOWED_RESOURCE, $allowResource);
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
    public function setExtensionAttributes(AccessTokenExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
