<?php
namespace Aheadworks\Sarp2\Gateway\BamboraApac;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\PaymentData\BamboraApac\Transaction\ToToken as TransactionToToken;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TokenAssigner
 *
 * @package Aheadworks\Sarp2\Gateway\BamboraApac
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
     * @var TransactionToToken
     */
    private $ccToTokenConverter;

    /**
     * @param Finder $tokenFinder
     * @param PaymentTokenRepositoryInterface $tokenRepository
     * @param TransactionToToken $ccToTokenConverter
     */
    public function __construct(
        Finder $tokenFinder,
        PaymentTokenRepositoryInterface $tokenRepository,
        TransactionToToken $ccToTokenConverter
    ) {
        $this->tokenFinder = $tokenFinder;
        $this->tokenRepository = $tokenRepository;
        $this->ccToTokenConverter = $ccToTokenConverter;
    }

    /**
     * Assign payment token using transaction response
     *
     * @param InfoInterface $payment
     * @param \Aheadworks\BamboraApac\Model\Api\Result\Response $transaction
     * @return InfoInterface
     * @throws \Exception
     */
    public function assignByTransaction(InfoInterface $payment, $transaction)
    {
        $token = $this->getTokenToAssign(
            $this->ccToTokenConverter->convert($transaction)
        );
        $payment->setAdditionalInformation('aw_sarp_payment_token_id', $token->getTokenId());
        return $payment;
    }

    /**
     * Get token to assign
     *
     * @param PaymentTokenInterface $candidate
     * @return PaymentTokenInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
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
