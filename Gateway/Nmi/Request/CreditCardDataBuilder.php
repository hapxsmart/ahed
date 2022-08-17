<?php
namespace Aheadworks\Sarp2\Gateway\Nmi\Request;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Gateway\Nmi\SubjectReaderFactory;
use Aheadworks\Sarp2\Observer\NmiRecurring\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class CreditCardDataBuilder
 *
 * @package Aheadworks\Sarp2\Gateway\Nmi\Request
 */
class CreditCardDataBuilder implements BuilderInterface
{
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

        return [
            \Aheadworks\Nmi\Gateway\Request\CreditCardDataBuilder::CUSTOMER_VAULT_ID => $gatewayToken
        ];
    }
}
