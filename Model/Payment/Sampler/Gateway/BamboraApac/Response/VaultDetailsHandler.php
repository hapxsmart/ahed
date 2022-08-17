<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\Gateway\BamboraApac\SubjectReaderFactory as BamboraApacSubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\BamboraApac\TokenAssigner;

/**
 * Class VaultDetailsHandler
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Response
 */
class VaultDetailsHandler implements HandlerInterface
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
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param SubjectReader $subjectReader
     * @param BamboraApacSubjectReaderFactory $bamboraApacSubjectReaderFactory
     * @param TokenAssigner $tokenAssigner
     */
    public function __construct(
        SubjectReader $subjectReader,
        BamboraApacSubjectReaderFactory $bamboraApacSubjectReaderFactory,
        TokenAssigner $tokenAssigner
    ) {
        $this->subjectReader = $subjectReader;
        $this->bamboraApacSubjectReaderFactory = $bamboraApacSubjectReaderFactory;
        $this->tokenAssigner = $tokenAssigner;
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
        $payment = $paymentDO->getPayment();

        $bamboraApacSubjectReader = $this->bamboraApacSubjectReaderFactory->getInstance();
        if ($bamboraApacSubjectReader) {
            $transactionResponse = $bamboraApacSubjectReader->readTransactionResponse($response);
            $this->tokenAssigner->assignByTransaction($payment, $transactionResponse);
        }
    }
}
