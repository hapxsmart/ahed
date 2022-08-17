<?php
namespace Aheadworks\Sarp2\PaymentData\Nmi\Transaction;

/**
 * Class ExpirationDate
 *
 * @package Aheadworks\Sarp2\PaymentData\Nmi\Transaction
 */
class ExpirationDate
{
    /**
     * Get formatted credit card expiration date
     *
     * @param \Aheadworks\Nmi\Model\Api\Result\Response $transaction
     * @return string
     * @throws \Exception
     */
    public function getFormatted($transaction)
    {
        $time = sprintf('%s-%s-01 00:00:00', $transaction->getExpiredInYear(), $transaction->getExpiredInMonth());
        return (new \DateTime($time))
            ->add(new \DateInterval('P1M'))
            ->format('Y-m-d 00:00:00');
    }
}
