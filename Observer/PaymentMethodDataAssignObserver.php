<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PaymentMethodDataAssignObserver
 * @package Aheadworks\Sarp2\Observer
 */
class PaymentMethodDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param IntegratedMethodList $integratedMethodList
     */
    public function __construct(
        IntegratedMethodList $integratedMethodList
    ) {
        $this->integratedMethodList = $integratedMethodList;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (is_array($additionalData)) {
            $paymentInfo = $this->readPaymentModelArgument($observer);
            $code = $this->readMethodArgument($observer)->getCode();
            foreach ($this->integratedMethodList->getList() as $integratedMethod) {
                switch ($code) {
                    case $integratedMethod->getCode():
                        $integratedMethod->getPaymentDataAssigner()->assignDataToBaseMethod(
                            $paymentInfo,
                            $additionalData
                        );
                        break;
                    case $integratedMethod->getRecurringCode():
                        $integratedMethod->getPaymentDataAssigner()->assignDataToRecurringMethod(
                            $paymentInfo,
                            $additionalData
                        );
                        break;

                }
            }
        }
    }
}
