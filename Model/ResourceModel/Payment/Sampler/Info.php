<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Info
 * @package Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler
 */
class Info extends AbstractDb
{
    /**
     * Id field name
     */
    const ID_FIELD_NAME = 'sampler_id';

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['additional_information' => [null, [], true]];

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
        $this->_init('aw_sarp2_payment_sampler', self::ID_FIELD_NAME);
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
}
