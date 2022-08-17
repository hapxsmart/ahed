<?php
namespace Aheadworks\Sarp2\PaymentData\BamboraApac\Transaction;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class CreditCardType
 *
 * @package Aheadworks\Sarp2\PaymentData\BamboraApac\Transaction
 */
class CreditCardType
{
    /**
     * @var ConfigInterface
     */
    private $bamboraApacGatewayConfig;

    /**
     * @param ConfigInterface $bamboraApacGatewayConfig
     */
    public function __construct(
        ConfigInterface $bamboraApacGatewayConfig
    ) {
        $this->bamboraApacGatewayConfig = $bamboraApacGatewayConfig;
    }

    /**
     * Get prepared credit card type
     *
     * @param \Aheadworks\BamboraApac\Model\Api\Result\Response $transactionResponse
     * @return string
     */
    public function getPrepared($transactionResponse)
    {
        $creditCardType = $transactionResponse->getCardType();
        $replacedCreditCardType = str_replace(' ', '-', strtolower($creditCardType));
        $mapper = $this->bamboraApacGatewayConfig->getCctypesMapper();
        return isset($mapper[$replacedCreditCardType]) ? $mapper[$replacedCreditCardType] : '';
    }
}
