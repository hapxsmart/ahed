<?php
namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Response;

use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\ProfileAdapter;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\SubjectReaderFactory;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\TokenAssigner;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Class ProfileDetailsHandler
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Response
 */
class ProfileDetailsHandler implements HandlerInterface
{
    /**
     * Create profile by transaction command code
     */
    const CREATE_PROFILE_BY_TRANSACTION = 'create_profile_by_transaction';

    /**
     * @var SubjectReaderFactory
     */
    private $subjectReaderFactory;

    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @var ProfileAdapter
     */
    private $profileAdapter;

    /**
     * @param SubjectReaderFactory $subjectReaderFactory
     * @param TokenAssigner $tokenAssigner
     * @param ProfileAdapter $profileAdapter
     */
    public function __construct(
        SubjectReaderFactory $subjectReaderFactory,
        TokenAssigner $tokenAssigner,
        ProfileAdapter $profileAdapter
    ) {
        $this->subjectReaderFactory = $subjectReaderFactory;
        $this->tokenAssigner = $tokenAssigner;
        $this->profileAdapter = $profileAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var SubjectReader $subjectReader */
        $subjectReader = $this->subjectReaderFactory->getInstance();
        $paymentDO = $subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();

        $isPaymentTokenEnabled = $payment->getAdditionalInformation('is_aw_sarp_payment_token_enabled');
        if ($isPaymentTokenEnabled) {
            $transactionId = $response['transactionResponse']['transId'];
            $profileData = $this->profileAdapter->execute(
                self::CREATE_PROFILE_BY_TRANSACTION,
                ['transactionId' => $transactionId]
            )->get();
            $this->tokenAssigner->assignByProfileDetails($payment, $profileData);
        }
    }
}
