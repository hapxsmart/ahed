<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\PrePaymentInfoInterface;
use Aheadworks\Sarp2\Api\Data\PrePaymentInfoExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class PrePaymentInfo
 * @package Aheadworks\Sarp2\Model\Profile
 */
class PrePaymentInfo extends AbstractExtensibleObject implements PrePaymentInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIsInitialFeePaid()
    {
        return $this->_get(self::IS_INITIAL_FEE_PAID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsInitialFeePaid($isInitialFeePaid)
    {
        return $this->setData(self::IS_INITIAL_FEE_PAID, $isInitialFeePaid);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsTrialPaid()
    {
        return $this->_get(self::IS_TRIAL_PAID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsTrialPaid($isTrialPaid)
    {
        return $this->setData(self::IS_TRIAL_PAID, $isTrialPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsRegularPaid()
    {
        return $this->_get(self::IS_REGULAR_PAID);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsRegularPaid($isRegularPaid)
    {
        return $this->setData(self::IS_REGULAR_PAID, $isRegularPaid);
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
    public function setExtensionAttributes(PrePaymentInfoExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
