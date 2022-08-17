<?php
namespace Aheadworks\Sarp2\Engine\Payment\Evaluation;

/**
 * Class PaymentDetails
 * @package Aheadworks\Sarp2\Engine\Payment\Evaluation
 */
class PaymentDetails
{
    /**
     * @var string
     */
    private $paymentPeriod;

    /**
     * @var string
     */
    private $paymentType;

    /**
     * @var string
     */
    private $date;

    /**
     * @var float
     */
    private $totalAmount;

    /**
     * @var float
     */
    private $baseTotalAmount;

    /**
     * @param string $paymentPeriod
     * @param string $paymentType
     * @param string $date
     * @param float $totalAmount
     * @param float $baseTotalAmount
     */
    public function __construct(
        $paymentPeriod,
        $paymentType,
        $date,
        $totalAmount,
        $baseTotalAmount
    ) {
        $this->paymentPeriod = $paymentPeriod;
        $this->paymentType = $paymentType;
        $this->date = $date;
        $this->totalAmount = $totalAmount;
        $this->baseTotalAmount = $baseTotalAmount;
    }

    /**
     * Get payment period
     *
     * @return string
     */
    public function getPaymentPeriod()
    {
        return $this->paymentPeriod;
    }

    /**
     * Get payment type
     *
     * @return string
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Get payment date
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get total amount in profile currency
     *
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Get total amount in base currency
     *
     * @return float
     */
    public function getBaseTotalAmount()
    {
        return $this->baseTotalAmount;
    }
}
