<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TransactionIdDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class TransactionIdDataBuilder implements BuilderInterface
{
    /**
     * Transaction id
     */
    const TRANSACTION_ID = 'transactionid';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();

        $transactionId = $payment->getLastTransactionId();
        if (!$transactionId) {
            throw new LocalizedException(__('No authorization transaction to proceed capture.'));
        }

        return [
            self::TRANSACTION_ID => $transactionId
        ];
    }
}
