<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler;

use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Aheadworks\Sarp2\Model\ProfileRepository;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info as InfoResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Payment\Model\MethodInterface;

/**
 * Class Info
 * @package Aheadworks\Sarp2\Model\Payment\Sampler
 */
class Info extends AbstractModel implements SamplerInfoInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = InfoResource::ID_FIELD_NAME;

    /**
     * @var MethodInterface
     */
    private $paymentMethodInstance;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var PaymentData
     */
    private $paymentData;

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param EncryptorInterface $encryptor
     * @param PaymentData $paymentData
     * @param ProfileRepository $profileRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EncryptorInterface $encryptor,
        PaymentData $paymentData,
        ProfileRepository $profileRepository,
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
        $this->encryptor = $encryptor;
        $this->paymentData = $paymentData;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(InfoResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt($data)
    {
        return $this->encryptor->encrypt($data);
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt($data)
    {
        return $this->encryptor->decrypt($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->getData('method');
    }

    /**
     * {@inheritdoc}
     */
    public function setMethod($method)
    {
        return $this->setData('method', $method);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastTransactionId()
    {
        return $this->getData('last_transaction_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setLastTransactionId($lastTransactionId)
    {
        return $this->setData('last_transaction_id', $lastTransactionId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteId()
    {
        return $this->getData('quote_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData('quote_id', $quoteId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData('amount');
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData('amount', $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountPlaced()
    {
        return $this->getData('amount_placed');
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountPlaced($amountPlaced)
    {
        return $this->setData('amount_placed', $amountPlaced);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmountReverted()
    {
        return $this->getData('amount_reverted');
    }

    /**
     * {@inheritdoc}
     */
    public function setAmountReverted($amountReverted)
    {
        return $this->setData('amount_reverted', $amountReverted);
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrencyCode()
    {
        return $this->getData('currency_code');
    }

    /**
     * {@inheritdoc}
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData('currency_code', $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemoteIp()
    {
        return $this->getData('remote_ip');
    }

    /**
     * {@inheritdoc}
     */
    public function setRemoteIp($remoteIp)
    {
        return $this->setData('remote_ip', $remoteIp);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData('profile_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData('profile_id', $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfile()
    {
        if ($this->getData('profile') === null) {
            $profile = $this->profileRepository->get($this->getProfileId());
            $this->setData('profile', $profile);
        }
        return $this->getData('profile');
    }

    /**
     * {@inheritdoc}
     */
    public function setProfile($profile)
    {
        return $this->setData('profile', $profile);
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->getData('order');
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($order)
    {
        return $this->setData('order', $order);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setAdditionalInformation($key, $value = null)
    {
        if (is_array($key) && $value === null) {
            return $this->setData('additional_information', $key);
        } else {
            $additionalInformation = $this->getAdditionalInformation();
            $additionalInformation[$key] = $value;
            return $this->setAdditionalInformation($additionalInformation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasAdditionalInformation($key = null)
    {
        $additionalInformation = $this->getAdditionalInformation();
        return $key === null ?
            !empty($additionalInformation)
            : isset($additionalInformation[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function unsAdditionalInformation($key = null)
    {
        if ($key === null) {
            return $this->unsetData('additional_information');
        } else {
            $additionalInformation = $this->getAdditionalInformation();
            if (isset($additionalInformation[$key])) {
                unset($additionalInformation[$key]);
            }
            return $this->setAdditionalInformation($additionalInformation);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalInformation($key = null)
    {
        $additionalInformation = $this->getData('additional_information') ? : [];
        return $key === null
            ? $additionalInformation
            : (isset($additionalInformation[$key]) ? $additionalInformation[$key] : null);
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodInstance()
    {
        if (!$this->paymentMethodInstance) {
            $methodCode = $this->getMethod();
            if (!$methodCode) {
                throw new LocalizedException(__('The payment method you requested is not available.'));
            }
            $this->paymentMethodInstance = $this->paymentData->getMethodInstance($methodCode);
            $this->paymentMethodInstance->setInfoInstance($this);
            $this->paymentMethodInstance->setStore($this->getStoreId());
        }
        return $this->paymentMethodInstance;
    }
}
