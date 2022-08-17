<?php
namespace Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails;

use Aheadworks\Sarp2\Model\Payment\Token;
use Aheadworks\Sarp2\PaymentData\Create\Result;
use Aheadworks\Sarp2\PaymentData\Create\ResultFactory;

/**
 * Class ToCreateResult
 * @package Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails
 */
class ToCreateResult
{
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ResultFactory $resultFactory
    ) {
        $this->resultFactory = $resultFactory;
    }

    /**
     * Convert profile detail into create token result
     *
     * @param array $profileDetails
     * @return Result
     */
    public function convert($profileDetails)
    {
        return $this->resultFactory->create(
            [
                'tokenType' => Token::TOKEN_TYPE_CARD,
                'gatewayToken' => $this->resolvePaymentProfileId($profileDetails),
                'additionalData' => [
                    'customerProfileId' => $profileDetails['customerProfileId']
                ]
            ]
        );
    }

    /**
     * Get PaymentProfileId from profile data
     *
     * @param array $profileDetails
     * @return string|null
     */
    private function resolvePaymentProfileId($profileDetails)
    {
        if (isset($profileDetails['customerPaymentProfileIdList'])) {
            return reset($profileDetails['customerPaymentProfileIdList']);
        } elseif (isset($profileDetails['customerPaymentProfileId'])) {
            return $profileDetails['customerPaymentProfileId'];
        }

        return null;
    }
}
