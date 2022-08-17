<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\Gateway\BamboraApac\SubjectReaderFactory as BamboraApacSubjectReaderFactory;

/**
 * Class TransactionIdHandler
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Response
 */
class TransactionIdHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var BamboraApacSubjectReaderFactory
     */
    private $bamboraApacSubjectReaderFactory;

    /**
     * @param SubjectReader $subjectReader
     * @param BamboraApacSubjectReaderFactory $bamboraApacSubjectReaderFactory
     */
    public function __construct(
        SubjectReader $subjectReader,
        BamboraApacSubjectReaderFactory $bamboraApacSubjectReaderFactory
    ) {
        $this->subjectReader = $subjectReader;
        $this->bamboraApacSubjectReaderFactory = $bamboraApacSubjectReaderFactory;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $command = $this->subjectReader->readCommand($handlingSubject);
        $payment = $paymentDO->getPayment();

        $bamboraApacSubjectReader = $this->bamboraApacSubjectReaderFactory->getInstance();
        if ($bamboraApacSubjectReader) {
            $transactionResponse = $bamboraApacSubjectReader->readTransactionResponse($response);
            $transactionId = $transactionResponse->getReceipt();

            $payment->setLastTransactionId($transactionId);
            $payment->setAdditionalInformation($command . '_txn_id', $transactionId);
        }
    }
}
