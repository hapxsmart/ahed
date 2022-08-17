<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleExtensionInterface;
use Magento\Framework\Api\AbstractExtensibleObject;

/**
 * Class Title
 * @package Aheadworks\Sarp2\Model\Plan
 */
class Title extends AbstractExtensibleObject implements PlanTitleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPlanId()
    {
        return $this->_get(self::PLAN_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPlanId($planId)
    {
        return $this->setData(self::PLAN_ID, $planId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
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
    public function setExtensionAttributes(PlanTitleExtensionInterface $extensionAttributes)
    {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
