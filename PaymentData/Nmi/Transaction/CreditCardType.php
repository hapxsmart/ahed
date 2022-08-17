<?php
namespace Aheadworks\Sarp2\PaymentData\Nmi\Transaction;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class CreditCardType
 *
 * @package Aheadworks\Sarp2\PaymentData\Nmi\Transaction
 */
class CreditCardType
{
    /**
     * @var ConfigInterface
     */
    private $nmiGatewayConfig;

    /**
     * @param ConfigInterface $gatewayConfig
     */
    public function __construct(
        ConfigInterface $gatewayConfig
    ) {
        $this->nmiGatewayConfig = $gatewayConfig;
    }

    /**
     * Get prepared credit card type
     *
     * @param \Aheadworks\Nmi\Model\Api\Result\Response $transactionResponse
     * @return string
     */
    public function getPrepared($transactionResponse)
    {
        $creditCardType = $transactionResponse->getCardType();
        $replacedCreditCardType = str_replace(' ', '-', strtolower($creditCardType));
        $mapper = $this->nmiGatewayConfig->getCctypesMapper();
        return isset($mapper[$replacedCreditCardType]) ? $mapper[$replacedCreditCardType] : '';
    }
}
