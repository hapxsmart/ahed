<?php
namespace Aheadworks\Sarp2\Model\Payment\Method;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CashondeliveryRecurring
 *
 * @package Aheadworks\Sarp2\Model\Payment\Method
 */
class CashondeliveryRecurring extends \Magento\OfflinePayments\Model\Cashondelivery
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'aw_sarp_cashondelivery_recurring';

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context, 
            $registry, 
            $extensionFactory, 
            $customAttributeFactory, 
            $paymentData, 
            $scopeConfig, 
            $logger, 
            $resource, 
            $resourceCollection, 
            $data, 
            $directory
        );
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        try {
            $methodInstance = $this->paymentHelper->getMethodInstance(
                \Magento\OfflinePayments\Model\Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
            );
            return $methodInstance->getTitle();
        } catch (LocalizedException $exception) {
            return parent::getTitle();
        }
    }
}
