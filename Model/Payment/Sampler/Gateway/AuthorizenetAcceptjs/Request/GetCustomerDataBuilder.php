<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;

/**
 * Class GetCustomerDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request
 */
class GetCustomerDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @param SubjectReader $subjectReader
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        SubjectReader $subjectReader,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $profile = $paymentDO->getProfile();
        $paymentTokenId = $profile->getPaymentTokenId();
        $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);

        return [
            'customerProfileId' => $paymentToken->getDetails('customerProfileId'),
            'customerPaymentProfileId' => $paymentToken->getTokenValue()
        ];
    }
}
