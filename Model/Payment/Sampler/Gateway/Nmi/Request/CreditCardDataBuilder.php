<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request;

use Aheadworks\Nmi\Observer\DataAssignObserver;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Framework\Stdlib\BooleanUtils;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class CreditCardDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\Nmi\Request
 */
class CreditCardDataBuilder implements BuilderInterface
{
    /**#@+
     * Credit card block names
     */
    const PAYMENT_TOKEN = 'payment_token';
    const CUSTOMER_VAULT_ID = 'customer_vault_id';
    /**#@-*/

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param BooleanUtils $booleanUtils
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader,
        BooleanUtils $booleanUtils,
        PaymentTokenManagementInterface $tokenManagement
    ) {
        $this->subjectReader = $subjectReader;
        $this->booleanUtils = $booleanUtils;
        $this->tokenManagement = $tokenManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $profile = $paymentDO->getProfile();
        $payment = $paymentDO->getPayment();

        $token = $payment->getAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_TOKEN);
        $isVaultProcessed = $payment->getAdditionalInformation(DataAssignObserver::IS_VAULT);
        if ($isVaultProcessed && $this->booleanUtils->toBoolean($isVaultProcessed)) {
            $publicHash = $token;
            $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $profile->getCustomerId());
            if (!$paymentToken) {
                throw new \Exception('No available payment tokens');
            }
            $creditCardData[self::CUSTOMER_VAULT_ID] = $paymentToken->getGatewayToken();
        } else {
            // one time token
            $creditCardData[self::PAYMENT_TOKEN] = $token;
        }

        return $creditCardData;
    }
}
