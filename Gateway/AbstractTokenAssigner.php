<?php
namespace Aheadworks\Sarp2\Gateway;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;

/**
 * Class AbstractTokenAssigner
 *
 * @package Aheadworks\Sarp2\Gateway
 */
abstract class AbstractTokenAssigner
{
    /**
     * Payment additional information value key
     */
    const SARP_PAYMENT_TOKEN_ID = 'aw_sarp_payment_token_id';
    const SARP_SKIP_PAYMENT_TOKEN = 'aw_sarp_skip_payment_token';

    /**
     * @var Finder
     */
    private $tokenFinder;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Save new or update existing token
     *
     * @param PaymentTokenInterface $token
     * @return PaymentTokenInterface
     * @throws LocalizedException
     */
    protected function saveNewOrUpdateToken($token)
    {
        $existing = $this->tokenFinder->findExisting($token);
        if (!$existing) {
            $token->setIsActive(true);
            $this->tokenRepository->save($token);
            return $token;
        }
        $token->setData($existing->getData());
        return $token;
    }

    /**
     * Save new token
     *
     * @param PaymentTokenInterface $token
     * @return PaymentTokenInterface
     * @throws LocalizedException
     */
    protected function saveNewToken($token)
    {
        $token->setIsActive(true);
        $this->tokenRepository->save($token);
        return $token;
    }

    /**
     * Update payment
     *
     * @param InfoInterface $payment
     * @param PaymentTokenInterface $token
     */
    protected function updatePayment($payment, $token)
    {
        $payment->setAdditionalInformation(self::SARP_PAYMENT_TOKEN_ID, $token->getTokenId());
    }
}
