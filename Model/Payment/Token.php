<?php
namespace Aheadworks\Sarp2\Model\Payment;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenExtensionInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Validator;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Token
 * @package Aheadworks\Sarp2\Model\Payment
 */
class Token extends AbstractModel implements PaymentTokenInterface
{
    /**
     * Card token type
     */
    const TOKEN_TYPE_CARD = 'card';
    const TOKEN_TYPE_ACCOUNT = 'account';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Validator $validator
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Validator $validator,
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
        $this->validator = $validator;
    }

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
    public function getTokenId()
    {
        return $this->getData(self::TOKEN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTokenId($tokenId)
    {
        return $this->setData(self::TOKEN_ID, $tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
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
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetails($key = null)
    {
        $details = $this->getData(self::DETAILS) ? : [];
        return $key === null
            ? $details
            : (isset($details[$key]) ? $details[$key] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function setDetails($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            return $this->setData(self::DETAILS, $key);
        } else {
            /** @var string $key */
            $details = $this->getDetails();
            $details[$key] = $value;
            return $this->setDetails($details);
        }
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
    public function setExtensionAttributes(PaymentTokenExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getValidationRulesBeforeSave()
    {
        return $this->validator;
    }
}
