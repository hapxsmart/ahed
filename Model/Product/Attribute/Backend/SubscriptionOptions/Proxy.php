<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions;

use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Backend\BackendInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Proxy
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions
 */
class Proxy extends AbstractBackend
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var BackendInterface|AbstractBackend
     */
    private $instance;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $moduleManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get backend model instance
     *
     * @return BackendInterface|AbstractBackend
     */
    private function getInstance()
    {
        if (!$this->instance) {
            $className = $this->moduleManager->isEnabled('Aheadworks_Sarp2')
                ? SubscriptionOptions::class
                : Dummy::class;
            $this->instance = $this->objectManager->create($className);
        }
        return $this->instance;
    }

    /**
     * {@inheritdoc}
     */
    public function setAttribute($attribute)
    {
        return $this->getInstance()->setAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute()
    {
        return $this->getInstance()->getAttribute();
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getInstance()->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function isStatic()
    {
        return $this->getInstance()->isStatic();
    }

    /**
     * {@inheritdoc}
     */
    public function getTable()
    {
        return $this->getInstance()->getTable();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityIdField()
    {
        return $this->getInstance()->getEntityIdField();
    }

    /**
     * {@inheritdoc}
     */
    public function setValueId($valueId)
    {
        return $this->getInstance()->setValueId($valueId);
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityValueId($entity, $valueId)
    {
        return $this->getInstance()
            ->setEntityValueId($entity, $valueId);
    }

    /**
     * {@inheritdoc}
     */
    public function getValueId()
    {
        return $this->getInstance()->getValueId();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityValueId($entity)
    {
        return $this->getInstance()->getEntityValueId($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultValue()
    {
        return $this->getInstance()->getDefaultValue();
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object)
    {
        return $this->getInstance()->validate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave($object)
    {
        return $this->getInstance()->afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($object)
    {
        return $this->getInstance()->beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    public function afterLoad($object)
    {
        return $this->getInstance()->afterLoad($object);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDelete($object)
    {
        return $this->getInstance()->beforeDelete($object);
    }

    /**
     * {@inheritdoc}
     */
    public function afterDelete($object)
    {
        return $this->getInstance()->afterDelete($object);
    }

    /**
     * {@inheritdoc}
     */
    public function getAffectedFields($object)
    {
        return $this->getInstance()->getAffectedFields($object);
    }

    /**
     * {@inheritdoc}
     */
    public function isScalar()
    {
        return $this->getInstance()->isScalar();
    }
}
