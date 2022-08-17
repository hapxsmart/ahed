<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class ScheduledPaymentInfo
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ScheduledPaymentInfo extends AbstractExtensibleObject implements ScheduledPaymentInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPaymentPeriod()
    {
        return $this->_get(self::PAYMENT_PERIOD);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentPeriod($paymentPeriod)
    {
        return $this->setData(self::PAYMENT_PERIOD, $paymentPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentStatus()
    {
        return $this->_get(self::PAYMENT_STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentStatus($paymentStatus)
    {
        return $this->setData(self::PAYMENT_STATUS, $paymentStatus);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentDate()
    {
        return $this->_get(self::PAYMENT_DATE);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentDate($paymentDate)
    {
        return $this->setData(self::PAYMENT_DATE, $paymentDate);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->setData(self::BASE_AMOUNT, $baseAmount);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(ScheduledPaymentInfoExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
