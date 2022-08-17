<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request;

use Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs\ProfileAdapter;
use Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails\ToToken as AuthorizeToken;
use Magento\Payment\Helper\Formatter;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;

/**
 * Class AuthorizeDataBuilder
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Gateway\AuthorizenetAcceptjs\Request
 */
class AuthorizeDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var ProfileAdapter
     */
    private $profileAdapter;

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
     * @param ProfileAdapter $profileAdapter
     * @param PaymentTokenRepositoryInterface $paymentTokenRepository
     */
    public function __construct(
        SubjectReader $subjectReader,
        ProfileAdapter $profileAdapter,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->subjectReader = $subjectReader;
        $this->profileAdapter = $profileAdapter;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $profile = $paymentDO->getProfile();
        $payment = $paymentDO->getPayment();

        if ($profile->getPaymentMethod() == AuthorizeToken::METHOD) {
            $this->profileAdapter->execute('update_customer_data', $buildSubject);
            $paymentTokenId = $profile->getPaymentTokenId();
            $paymentToken = $this->paymentTokenRepository->get($paymentTokenId);
            return [
                'transactionRequest' => [
                    'transactionType' => 'authOnlyTransaction',
                    'amount' => $this->formatPrice($payment->getAmount()),
                    'profile' => [
                        'customerProfileId' => $paymentToken->getDetails('customerProfileId'),
                        'paymentProfile' => [
                            'paymentProfileId' => $paymentToken->getTokenValue()
                        ]
                    ]
                ]
            ];
        } else {
            $additionalInformation = $payment->getAdditionalInformation();
            return [
                'transactionRequest' => [
                    'transactionType' => 'authOnlyTransaction',
                    'amount' => $this->formatPrice($payment->getAmount()),
                    'payment' => [
                        'opaqueData' => [
                            'dataDescriptor' => $additionalInformation['opaqueDataDescriptor'],
                            'dataValue' => $additionalInformation['opaqueDataValue']
                        ]
                    ],
                    'customer' => [
                        'id' => $profile->getCustomerId(),
                        'email' => $profile->getCustomerEmail()
                    ]
                ]
            ];
        }
    }
}
