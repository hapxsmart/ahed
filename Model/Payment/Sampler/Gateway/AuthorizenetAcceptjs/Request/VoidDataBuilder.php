<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class VoidDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request
 */
class VoidDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();

        return [
            'transactionRequest' => [
                'transactionType' => 'voidTransaction',
                'refTransId' => $payment->getAdditionalInformation('transId')
            ]
        ];
    }
}
