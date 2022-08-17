<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\ProfileAddressInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Address
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class Address extends AbstractDb
{
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
        $this->_init('aw_sarp2_profile_address', 'address_id');
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
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var ProfileAddressInterface|AbstractModel $object */
        $street = $object->getStreet();
        if (is_array($street)) {
            $street = implode("\n", $street);
            $object->setStreet($street);
        }
        return parent::_beforeSave($object);
    }
}
