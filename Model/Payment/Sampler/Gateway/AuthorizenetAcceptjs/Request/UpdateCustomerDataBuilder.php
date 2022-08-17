<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\ProfileAdapter;

/**
 * Class UpdateCustomerDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request
 */
class UpdateCustomerDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ProfileAdapter
     */
    private $profileAdapter;

    /**
     * @param SubjectReader $subjectReader
     * @param ProfileAdapter $profileAdapter
     */
    public function __construct(
        SubjectReader $subjectReader,
        ProfileAdapter $profileAdapter
    ) {
        $this->subjectReader = $subjectReader;
        $this->profileAdapter = $profileAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $additionalInformation = $payment->getAdditionalInformation();
        $customerData = $this->profileAdapter->execute('get_customer_data', $buildSubject);
        $paymentProfile = $customerData->get()['paymentProfile'];

        return [
            'customerProfileId' => $paymentProfile['customerProfileId'],
            'paymentProfile' => [
                'payment' => [
                    'opaqueData' => [
                        'dataDescriptor' => $additionalInformation['opaqueDataDescriptor'],
                        'dataValue' => $additionalInformation['opaqueDataValue']
                    ]
                ],
                'defaultPaymentProfile' => false,
                'customerPaymentProfileId' => $paymentProfile['customerPaymentProfileId']
            ]
        ];
    }
}
