<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Adapter;

use Aheadworks\Sarp2\Model\Payment\Sampler\Info\PaymentDataConverter;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\PaymentInterface as QuotePaymentInfo;

/**
 * Class PaymentInfoDataExtractor
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Adapter
 */
class PaymentInfoDataExtractor
{
    /**
     * @var PaymentDataConverter
     */
    private $paymentDataConverter;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param PaymentDataConverter $paymentDataConverter
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(PaymentDataConverter $paymentDataConverter, DataObjectProcessor $dataObjectProcessor)
    {
        $this->paymentDataConverter = $paymentDataConverter;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Get payment info data object
     *
     * @param $paymentInfo
     * @param string $paymentInfoType
     * @return \Magento\Framework\DataObject
     */
    public function getPaymentDataAndConvertToDataObject($paymentInfo, $paymentInfoType = QuotePaymentInfo::class)
    {
        return $this->paymentDataConverter->convert(
            $this->getPaymentData($paymentInfo, $paymentInfoType)
        );
    }

    /**
     * Get payment info data array
     *
     * @param QuotePaymentInfo $paymentInfo
     * @param string $paymentInfoType
     * @return array
     */
    private function getPaymentData(QuotePaymentInfo $paymentInfo, string $paymentInfoType)
    {
        $data = $this->dataObjectProcessor->buildOutputDataArray(
            $paymentInfo,
            $paymentInfoType
        );
        $data['additional_data'] = $paymentInfo->getAdditionalData();

        return $data;
    }
}
