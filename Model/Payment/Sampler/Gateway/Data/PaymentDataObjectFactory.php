<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data;

use Aheadworks\Sarp2\Model\Payment\SamplerInfoInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class PaymentDataObjectFactory
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Data
 */
class PaymentDataObjectFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Creates Payment Data Object
     *
     * @param SamplerInfoInterface $paymentInfo
     * @return PaymentDataObjectInterface
     */
    public function create(SamplerInfoInterface $paymentInfo)
    {
        $data = [
            'profile' => $paymentInfo->getProfile(),
            'payment' => $paymentInfo
        ];

        return $this->objectManager->create(
            PaymentDataObject::class,
            $data
        );
    }
}
