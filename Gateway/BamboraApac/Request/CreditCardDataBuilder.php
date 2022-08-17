<?php
namespace Aheadworks\Sarp2\Gateway\BamboraApac\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Gateway\BamboraApac\SubjectReaderFactory;
use Aheadworks\Sarp2\Observer\BamboraApacRecurring\DataAssignObserver;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;

/**
 * Class CreditCardDataBuilder
 *
 * @package Aheadworks\Sarp2\Gateway\BamboraApac\Request
 */
class CreditCardDataBuilder implements BuilderInterface
{
    /**#@+
     * Credit card block names
     */
    const CREDIT_CARD = 'CreditCard';
    const ONE_TIME_TOKEN = 'OneTimeToken';
    const TOKENISE_ALGORITHM_ID = 'TokeniseAlgorithmID';
    const TOKENISE_ALGORITHM_ID_VALUE = 2;
    const CARD_NUMBER = 'CardNumber';
    /**#@-*/

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        PaymentTokenManagementInterface $tokenManagement,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->tokenManagement = $tokenManagement;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function build(array $buildSubject)
    {
        $subjectReader = $this->subjectReaderFactory->getInstance();
        if (!$subjectReader) {
            return [];
        }
        $paymentDO = $subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $paymentTokenId = $payment->getAdditionalInformation(DataAssignObserver::PAYMENT_TOKEN_ID);
        if (!$paymentTokenId) {
            throw new \LogicException('Payment token ID is not specified.');
        }

        $gatewayToken = $this->paymentTokenRepository->get($paymentTokenId)->getTokenValue();
        $creditCardData = [
            self::TOKENISE_ALGORITHM_ID => self::TOKENISE_ALGORITHM_ID_VALUE,
            self::CARD_NUMBER => $gatewayToken
        ];

        return [
            self::CREDIT_CARD => $creditCardData
        ];
    }
}
