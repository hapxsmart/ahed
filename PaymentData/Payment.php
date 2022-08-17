<?php
namespace Aheadworks\Sarp2\PaymentData;

use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Model\Quote;

/**
 * Class Payment
 * @package Aheadworks\Sarp2\PaymentData
 */
class Payment implements PaymentInterface
{
    /**
     * @var InfoInterface
     */
    private $paymentInfo;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @param InfoInterface $paymentInfo
     * @param Quote $quote
     */
    public function __construct(
        InfoInterface $paymentInfo,
        Quote $quote
    ) {
        $this->paymentInfo = $paymentInfo;
        $this->quote = $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentInfo()
    {
        return $this->paymentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuote()
    {
        return $this->quote;
    }
}
