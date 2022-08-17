<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Item
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class Item extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    protected $_serializableFields = ['product_options' => [[], []]];

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile_item', 'item_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(PlanInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * @inheritDoc
     */
    protected function _serializeField(DataObject $object, $field, $defaultValue = null, $unsetEmpty = false)
    {
        $value = $object->getData($field);
        if (is_string($value) && $field == 'product_options') {
            return $this;
        }

        return parent::_serializeField($object, $field, $defaultValue, $unsetEmpty);
    }
}
