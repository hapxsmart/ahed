<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean as BooleanSource;

/**
 * Class BooleanWithConfig
 *
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Backend
 */
class BooleanWithConfig extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @inheritDoc
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($object->getData('use_config_' . $attributeCode)) {
            $object->setData($attributeCode, BooleanSource::VALUE_USE_CONFIG);
            return $this;
        }

        return parent::beforeSave($object);
    }
}
