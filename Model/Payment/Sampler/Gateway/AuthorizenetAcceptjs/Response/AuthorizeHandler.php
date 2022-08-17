<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Response;

use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\ProfileAdapter;
use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails\ToToken as AuthorizeToken;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\Response\ProfileDetailsHandler;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\TokenAssigner;

/**
 * Class AuthorizeHandler
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Response
 */
class AuthorizeHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var TokenAssigner
     */
    private $tokenAssigner;

    /**
     * @var ProfileAdapter
     */
    private $profileAdapter;

    /**
     * @param SubjectReader $subjectReader
     * @param TokenAssigner $tokenAssigner
     * @param ProfileAdapter $profileAdapter
     */
    public function __construct(
        SubjectReader $subjectReader,
        TokenAssigner $tokenAssigner,
        ProfileAdapter $profileAdapter
    ) {
        $this->subjectReader = $subjectReader;
        $this->tokenAssigner = $tokenAssigner;
        $this->profileAdapter = $profileAdapter;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDO->getPayment();
        $profile = $paymentDO->getProfile();
        $transactionResponse = $response['transactionResponse'];
        $transactionId = $transactionResponse['transId'];

        if ($profile->getPaymentMethod() == AuthorizeToken::METHOD) {
            $profileData = $transactionResponse['profile'];
        } else {
            $profileData = $this->profileAdapter->execute(
                ProfileDetailsHandler::CREATE_PROFILE_BY_TRANSACTION,
                ['transactionId' => $transactionId]
            )->get();
        }

        $payment->setAdditionalInformation('transId', $transactionId);
        $payment->unsAdditionalInformation('opaqueDataDescriptor');
        $payment->unsAdditionalInformation('opaqueDataValue');
        $this->tokenAssigner->assignTokenBySamplerProfileDetails($payment, $profileData);
    }
}
