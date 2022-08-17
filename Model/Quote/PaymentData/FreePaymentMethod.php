<?php
namespace Aheadworks\Sarp2\Model\Quote\PaymentData;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Payment\Model\Method\Free;

/**
 * Class FreePaymentMethod
 *
 * @package Aheadworks\Sarp2\Model\Quote\PaymentData
 */
class FreePaymentMethod
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param PaymentInterfaceFactory $paymentFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        PaymentInterfaceFactory $paymentFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * Get free payment method details
     *
     * @param PaymentInterface $paymentMethod
     * @return PaymentInterface
     */
    public function getDetails($paymentMethod)
    {
        /** @var PaymentInterface $payment */
        $payment = $this->paymentFactory->create();
        $freePaymentMethod = [
            'method' => Free::PAYMENT_METHOD_FREE_CODE
        ];
        $this->dataObjectHelper->populateWithArray(
            $payment,
            $freePaymentMethod,
            PaymentInterface::class
        );
        $paymentExtensionAttributes = $paymentMethod->getExtensionAttributes();
        if ($paymentExtensionAttributes) {
            $payment->setExtensionAttributes($paymentExtensionAttributes);
        }

        return $payment;
    }
}
