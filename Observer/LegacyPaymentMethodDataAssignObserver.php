<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Payment\Method\Data\DataAssignerInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class LegacyPaymentMethodDataAssignObserver
 *
 * @package Aheadworks\Sarp2\Observer
 * @deprecated
 */
class LegacyPaymentMethodDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (is_array($additionalData)) {
            $paymentInfo = $this->readPaymentModelArgument($observer);
            if (isset($additionalData[DataAssignerInterface::IS_SARP_TOKEN_ENABLED])) {
                $paymentInfo->setAdditionalInformation(
                    DataAssignerInterface::IS_SARP_TOKEN_ENABLED,
                    $additionalData[DataAssignerInterface::IS_SARP_TOKEN_ENABLED]
                );
            }
        }
    }
}
