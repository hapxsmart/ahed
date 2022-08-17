<?php
namespace Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Payment\Model\CcConfig;
use Magento\Payment\Model\InfoInterface;

/**
 * Class ToToken
 * @package Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails
 */
class ToToken
{
    /**
     * Method code
     */
    const METHOD = 'authorizenet_acceptjs';

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var ToCreateResult
     */
    private $toCreateResult;

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param ToCreateResult $toCreateResult
     * @param CcConfig $ccConfig
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        ToCreateResult $toCreateResult,
        CcConfig $ccConfig
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->toCreateResult = $toCreateResult;
        $this->ccConfig = $ccConfig;
    }

    /**
     * Convert profile details into payment token
     *
     * @param array $profileDetails
     * @param InfoInterface $payment
     * @return PaymentTokenInterface
     */
    public function convert($profileDetails, $payment)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->tokenFactory->create();
        $paymentData = $this->toCreateResult->convert($profileDetails);
        $paymentToken
            ->setPaymentMethod(self::METHOD)
            ->setType($paymentData->getTokenType())
            ->setTokenValue($paymentData->getGatewayToken())
            ->setDetails('customerProfileId', $paymentData->getAdditionalData()->getData('customerProfileId'))
            ->setDetails('ccLast4', $payment->getAdditionalInformation('ccLast4'))
            ->setDetails('accountType', $this->getAccountType($payment->getAdditionalInformation('accountType')));

        return $paymentToken;
    }

    /**
     * Retrieve account type
     *
     * @param string $accountType
     * @return string
     */
    private function getAccountType($accountType)
    {
        $types = (array)$this->ccConfig->getCcAvailableTypes();

        return in_array($accountType, $types)
            ? array_search($accountType, $types)
            : $accountType;
    }
}
