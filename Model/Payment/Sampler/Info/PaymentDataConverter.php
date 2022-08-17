<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Info;

use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Class PaymentDataConverter
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Info
 */
class PaymentDataConverter
{
    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @param Factory $dataObjectFactory
     */
    public function __construct(Factory $dataObjectFactory)
    {
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Convert raw request data to payment data object
     *
     * @param array $data
     * @return DataObject
     */
    public function convert(array $data)
    {
        $paymentData = [
            PaymentInterface::KEY_METHOD => null,
            PaymentInterface::KEY_PO_NUMBER => null,
            PaymentInterface::KEY_ADDITIONAL_DATA => []
        ];
        foreach (array_keys($data) as $key) {
            if (!array_key_exists($key, $paymentData)) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA][$key] = $data[$key];
            } elseif ($key == PaymentInterface::KEY_ADDITIONAL_DATA) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA] = array_merge(
                    $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA],
                    (array)$data[$key]
                );
            } else {
                $paymentData[$key] = $data[$key];
            }
        }
        return $this->dataObjectFactory->create($paymentData);
    }
}
