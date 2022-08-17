<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class CreditCardDataBuilder
 *
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\BamboraApac\Request
 */
class CreditCardDataBuilder implements BuilderInterface
{
    /**
     * Constants from \Aheadworks\BamboraApac\Observer\DataAssignObserver
     */
    const PAYMENT_METHOD_TOKEN = 'payment_method_token';
    const IS_VAULT = 'is_vault';

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
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param SubjectReader $subjectReader
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(
        SubjectReader $subjectReader,
        PaymentTokenManagementInterface $tokenManagement,
        BooleanUtils $booleanUtils
    ) {
        $this->subjectReader = $subjectReader;
        $this->tokenManagement = $tokenManagement;
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $profile = $paymentDO->getProfile();

        $creditCardData = [
            self::TOKENISE_ALGORITHM_ID => self::TOKENISE_ALGORITHM_ID_VALUE
        ];

        $token = $payment->getAdditionalInformation(self::PAYMENT_METHOD_TOKEN);
        $isVaultProcessed = $payment->getAdditionalInformation(self::IS_VAULT);
        if ($isVaultProcessed && $this->booleanUtils->toBoolean($isVaultProcessed)) {
            $publicHash = $token;
            $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $profile->getCustomerId());
            if (!$paymentToken) {
                throw new \Exception('No available payment tokens');
            }
            $creditCardData[self::CARD_NUMBER] = $paymentToken->getGatewayToken();
        } else {
            $creditCardData[self::ONE_TIME_TOKEN] = $token;
        }

        return [
            self::CREDIT_CARD => $creditCardData
        ];
    }
}
