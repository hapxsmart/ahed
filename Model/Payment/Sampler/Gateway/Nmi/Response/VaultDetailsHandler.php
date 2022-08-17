<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Response;

use Aheadworks\Sarp2\Gateway\Nmi\SubjectReaderFactory as NmiSubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\Nmi\TokenAssigner;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**.
 * Class VaultDetailsHandler
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Response
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var NmiSubjectReaderFactory
     */
    private $nmiSubjectReaderFactory;

    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param SubjectReader $subjectReader
     * @param NmiSubjectReaderFactory $subjectReaderFactory
     * @param TokenAssigner $tokenAssigner
     */
    public function __construct(
        SubjectReader $subjectReader,
        NmiSubjectReaderFactory $subjectReaderFactory,
        TokenAssigner $tokenAssigner
    ) {
        $this->subjectReader = $subjectReader;
        $this->nmiSubjectReaderFactory = $subjectReaderFactory;
        $this->tokenAssigner = $tokenAssigner;
    }

    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        $subjectReader = $this->nmiSubjectReaderFactory->getInstance();
        if ($subjectReader) {
            $transactionResponse = $subjectReader->readTransactionResponse($response);
            $this->tokenAssigner->assignByTransaction($payment, $transactionResponse);
        }
    }
}
