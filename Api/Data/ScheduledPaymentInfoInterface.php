<?php
namespace Aheadworks\Sarp2\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface ScheduledPaymentInfoInterface
 * @package Aheadworks\Sarp2\Api\Data
 */
interface ScheduledPaymentInfoInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const PAYMENT_PERIOD = 'payment_period';
    const PAYMENT_STATUS = 'payment_status';
    const PAYMENT_DATE = 'payment_date';
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';
    /**#@-*/

    /**#@+
     * Payment statuses
     * @var string
     */
    const PAYMENT_STATUS_SCHEDULED = 'scheduled';
    const PAYMENT_STATUS_REATTEMPT = 'reattempt';
    const PAYMENT_STATUS_NO_PAYMENT = 'no_payment';
    const PAYMENT_STATUS_LAST_PERIOD_HOLDER = 'last_period_holder';
    /**#@-*/

    /**
     * Get payment period
     *
     * @return string|null
     */
    public function getPaymentPeriod();

    /**
     * Set payment period
     *
     * @param string $paymentPeriod
     * @return $this
     */
    public function setPaymentPeriod($paymentPeriod);

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPaymentStatus();

    /**
     * Set payment status
     *
     * @param string $paymentStatus
     * @return $this
     */
    public function setPaymentStatus($paymentStatus);

    /**
     * Get payment date
     *
     * @return string|null
     */
    public function getPaymentDate();

    /**
     * Set payment date
     *
     * @param string $paymentDate
     * @return $this
     */
    public function setPaymentDate($paymentDate);

    /**
     * Get payment amount in profile currency
     *
     * @return float
     */
    public function getAmount();

    /**
     * Set payment amount in profile currency
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get payment amount in base currency
     *
     * @return float
     */
    public function getBaseAmount();

    /**
     * Set payment amount in base currency
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount);

    /**
     * Retrieve existing extension attributes object or create a new one
     *
     * @return \Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoExtensionInterface $extensionAttributes
    );
}
