<?php
namespace Aheadworks\Sarp2\Gateway\Nmi;

use Aheadworks\Nmi\Observer\DataAssignObserver;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\Payment\Token\Finder;
use Aheadworks\Sarp2\PaymentData\Nmi\Transaction\ToToken as TransactionToToken;
use Exception;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TokenAssigner
 *
 * @package Aheadworks\Sarp2\Gateway\Nmi
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
     * @param DataObject $transaction
     * @return InfoInterface
     * @throws Exception
     */
    public function assignByTransaction($payment, $transaction)
    {
        $this->prepareTransaction($transaction, $payment);

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

    /**
     * Prepare transaction. Set details information
     *
     * @param DataObject $transaction
     * @param InfoInterface $payment
     * @throws Exception
     */
    private function prepareTransaction($transaction, $payment)
    {
        $transaction->setData(
            'card_type',
            $payment->getAdditionalInformation(DataAssignObserver::CARD_TYPE) ? : ''
        );
        $transaction->setData(
            'truncated_card',
            $payment->getAdditionalInformation(DataAssignObserver::CARD_NUMBER) ? : ''
        );

        list($expiredInMonth, $expiredInYear) = $this->getExpired($payment);
        $transaction->setData('expired_in_month', $expiredInMonth);
        $transaction->setData('expired_in_year', $expiredInYear);
    }

    /**
     * Retrieve expired date
     *
     * @param InfoInterface $payment
     * @return array
     * @throws Exception
     */
    private function getExpired($payment)
    {
        $expDate = $payment->getAdditionalInformation(DataAssignObserver::CARD_EXPIRATION);
        if ($expDate) {
            $expiredInMonth = substr($expDate, 0, 2);
            $currentYear = (new \DateTime())->format('Y');
            $expiredInYear = substr($currentYear, 0, 2) . substr($expDate, 2, 4);
        } else {
            $expiredInMonth = '01';
            $expiredInYear = (new \DateTime())->modify('+1 year')->format('Y');
        }

        return [$expiredInMonth, $expiredInYear];
    }
}
