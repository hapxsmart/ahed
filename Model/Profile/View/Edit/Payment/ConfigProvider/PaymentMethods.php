<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Aheadworks\Sarp2\Api\PaymentInfoManagementInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Checkout\Api\Data\PaymentDetailsInterface;

/**
 * Class DefaultConfig
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class PaymentMethods implements ConfigProviderInterface
{
    /**
     * @var PaymentInfoManagementInterface
     */
    private $paymentInfoManagement;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param PaymentInfoManagementInterface $paymentInfoManagement
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        PaymentInfoManagementInterface $paymentInfoManagement,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->paymentInfoManagement = $paymentInfoManagement;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Return configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        $paymentDetails = $this->paymentInfoManagement->getPaymentInfoForSubscription();
        $paymentDetailsAsArray = $this->dataObjectProcessor->buildOutputDataArray(
            $paymentDetails,
            PaymentDetailsInterface::class
        );
        $config['paymentMethods'] = $paymentDetailsAsArray['payment_methods'];

        return $config;
    }
}
