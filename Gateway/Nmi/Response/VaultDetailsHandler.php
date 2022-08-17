<?php
namespace Aheadworks\Sarp2\Gateway\Nmi\Response;

use Aheadworks\Sarp2\Gateway\Nmi\SubjectReaderFactory;
use Aheadworks\Sarp2\Gateway\Nmi\TokenAssigner;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class VaultDetailsHandler
 *
 * @package Aheadworks\Sarp2\Gateway\Nmi\Response
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @param TokenAssigner $tokenAssigner
     * @param SubjectReaderFactory $subjectReaderFactory
     */
    public function __construct(
        TokenAssigner $tokenAssigner,
        SubjectReaderFactory $subjectReaderFactory
    ) {
        $this->tokenAssigner = $tokenAssigner;
        $this->subjectReaderFactory = $subjectReaderFactory;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        if (!$subjectReader) {
            return null;
        }
        $paymentDO = $subjectReader->readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $isPaymentTokenEnabled = $payment->getAdditionalInformation('is_aw_sarp_payment_token_enabled');
        if ($isPaymentTokenEnabled) {
            $transaction = $subjectReader->readTransactionResponse($response);

            $this->tokenAssigner->assignByTransaction($payment, $transaction);
        }
    }
}
