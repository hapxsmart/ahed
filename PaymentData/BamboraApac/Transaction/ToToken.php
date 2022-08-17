<?php
namespace Aheadworks\Sarp2\PaymentData\BamboraApac\Transaction;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Model\Payment\Token;

/**
 * Class ToToken
 *
 * @package Aheadworks\Sarp2\PaymentData\BamboraApac\Transaction
 */
class ToToken
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var ExpirationDate
     */
    private $expirationDate;

    /**
     * @var CreditCardType
     */
    private $creditCardType;

    /**
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param ExpirationDate $expirationDate
     * @param CreditCardType $creditCardType
     */
    public function __construct(
        PaymentTokenInterfaceFactory $tokenFactory,
        ExpirationDate $expirationDate,
        CreditCardType $creditCardType
    ) {
        $this->tokenFactory = $tokenFactory;
        $this->expirationDate = $expirationDate;
        $this->creditCardType = $creditCardType;
    }

    /**
     * Convert credit card detail into payment token
     *
     * @param \Aheadworks\BamboraApac\Model\Api\Result\Response $transaction
     * @return PaymentTokenInterface
     * @throws \Exception
     */
    public function convert($transaction)
    {
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->tokenFactory->create();
        $paymentToken->setPaymentMethod('aw_bambora_apac')
            ->setType(Token::TOKEN_TYPE_CARD)
            ->setTokenValue($transaction->getCreditCardToken())
            ->setExpiresAt($this->expirationDate->getFormatted($transaction))
            ->setDetails('typeCode', $this->creditCardType->getPrepared($transaction))
            ->setDetails('lastCcNumber', substr($transaction->getTruncatedCard(), -4))
            ->setDetails('expirationDate', $transaction->getExpiredInMonth() . '/' . $transaction->getExpiredInYear());
        return $paymentToken;
    }
}
