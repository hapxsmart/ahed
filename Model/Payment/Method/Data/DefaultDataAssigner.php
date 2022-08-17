<?php
namespace Aheadworks\Sarp2\Model\Payment\Method\Data;

use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Magento\Payment\Model\InfoInterface;

/**
 * Class DefaultDataAssigner
 *
 * @package Aheadworks\Sarp2\Model\Payment\Method\Data
 */
class DefaultDataAssigner implements DataAssignerInterface
{
    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @param PaymentTokenRepositoryInterface $tokenRepository
     */
    public function __construct(PaymentTokenRepositoryInterface $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * @inheritDoc
     */
    public function assignDataToBaseMethod(InfoInterface $paymentInfo, array $additionalData)
    {
        if (isset($additionalData[self::IS_SARP_TOKEN_ENABLED])) {
            $paymentInfo->setAdditionalInformation(
                self::IS_SARP_TOKEN_ENABLED,
                $additionalData[self::IS_SARP_TOKEN_ENABLED]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignDataToRecurringMethod(InfoInterface $paymentInfo, array $additionalData)
    {
        if (isset($additionalData[self::TOKEN_ID])) {
            $paymentInfo->setAdditionalInformation(
                self::GATEWAY_TOKEN,
                $this->tokenRepository->get($additionalData[self::TOKEN_ID])
            );
        }
    }
}
