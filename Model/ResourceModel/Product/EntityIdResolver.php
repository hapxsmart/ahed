<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Catalog\Model\Product;

/**
 * Class EntityIdResolver
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel\Product
 */
class EntityIdResolver extends AbstractResource
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this
            ->setType(Product::ENTITY)
            ->setConnection($resource->getConnection('catalog'));
    }

    /**
     * Resolve entity ID
     *
     * @param int $entityId
     * @return int
     */
    public function resolve($entityId)
    {
        if ($this->getIdFieldName() == $this->getLinkField()) {
            return $entityId;
        }
        $select = $this->getConnection()->select();
        $tableName = $this->_resource->getTableName('catalog_product_entity');
        $select->from($tableName, [$this->getLinkField()])
            ->where('entity_id = ?', $entityId);
        return $this->getConnection()->fetchOne($select);
    }
}
