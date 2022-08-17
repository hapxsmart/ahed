<?php
namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\PaymentData\AuthorizenetAcceptjs\ProfileDetails\ToToken as ProfileDetailsToToken;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TokenAssigner
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs
 */
class TokenAssigner
{
    /**
     * @var Finder
     */
    private $tokenFinder;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var ProfileDetailsToToken
     */
    private $profileToTokenConverter;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param ProfileDetailsToToken $profileToTokenConverter
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        ProfileDetailsToToken $profileToTokenConverter
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->profileToTokenConverter = $profileToTokenConverter;
    }

    /**
     * Assign payment token using profile details
     *
     * @param InfoInterface $payment
     * @param array $profileDetails
     * @return InfoInterface
     * @throws LocalizedException
     */
    public function assignByProfileDetails(InfoInterface $payment, $profileDetails)
    {
        $token = $this->getTokenToAssign(
            $this->profileToTokenConverter->convert($profileDetails, $payment)
        );
        $payment->setAdditionalInformation('aw_sarp_payment_token_id', $token->getTokenId());
        return $payment;
    }

    /**
     * Assign payment token using profile details for sampler
     *
     * @param InfoInterface $payment
     * @param array $profileDetails
     * @return InfoInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function assignTokenBySamplerProfileDetails(InfoInterface $payment, $profileDetails)
    {
        $token = $this->profileToTokenConverter->convert($profileDetails, $payment);
        $token->setIsActive(true);
        $this->tokenRepository->save($token);
        $payment->setAdditionalInformation('aw_sarp_payment_token_id', $token->getTokenId());

        return $payment;
    }

    /**
     * Get token to assign
     *
     * @param PaymentTokenInterface $candidate
     * @return PaymentTokenInterface
     * @throws LocalizedException
     */
    private function getTokenToAssign($candidate)
    {
        $existing = $this->tokenFinder->findExisting($candidate);
        if (!$existing) {
            $candidate->setIsActive(true);
            $this->tokenRepository->save($candidate);
            return $candidate;
        }
        return $existing;
    }
}
