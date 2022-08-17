<?php
namespace Aheadworks\Sarp2\Model\ResourceModel;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile as ProfileModel;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Aheadworks\Sarp2\Model\ResourceModel\Definition\Loader as DefinitionLoader;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler\HandlerInterface;
use Aheadworks\Sarp2\Model\Profile\HashGenerator;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Profile
 */
class Profile extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SequenceManager
     */
    private $sequenceManager;

    /**
     * @var DefinitionLoader
     */
    private $definitionLoader;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @var HandlerInterface[]
     */
    private $saveHandlers = [];

    /**
     * @var HandlerInterface[]
     */
    private $deleteHandlers = [];

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param SequenceManager $sequenceManager
     * @param DefinitionLoader $definitionLoader
     * @param DataObjectProcessor $dataObjectProcessor
     * @param HashGenerator $hashGenerator
     * @param array $saveHandlers
     * @param array $deleteHandlers
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        SequenceManager $sequenceManager,
        DefinitionLoader $definitionLoader,
        DataObjectProcessor $dataObjectProcessor,
        HashGenerator $hashGenerator,
        $saveHandlers = [],
        $deleteHandlers = [],
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->sequenceManager = $sequenceManager;
        $this->definitionLoader = $definitionLoader;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->saveHandlers = $saveHandlers;
        $this->deleteHandlers = $deleteHandlers;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile', 'profile_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(ProfileInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * Get nearest profile id by customer id and store id
     *
     * @param int $customerId
     * @param int $storeId
     * @return int
     * @throws LocalizedException
     */
    public function getNearestProfileId($customerId, $storeId): int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getMainTable(),
                [
                    'aw_sarp2_core_schedule.profile_id',
                    'aw_sarp2_core_schedule_item.scheduled_at'
                ]
            )->joinLeft(
                ['aw_sarp2_core_schedule' => $this->getTable('aw_sarp2_core_schedule')],
                'aw_sarp2_core_schedule.' . ProfileInterface::PROFILE_ID . ' = '
                . $this->getMainTable() . '.' . ProfileInterface::PROFILE_ID,
                []
            )->joinLeft(
                ['aw_sarp2_core_schedule_item' => $this->getTable('aw_sarp2_core_schedule_item')],
                'aw_sarp2_core_schedule.schedule_id = aw_sarp2_core_schedule_item.schedule_id',
                []
            )->where(ProfileInterface::CUSTOMER_ID . ' = ?', $customerId)
            ->where(ProfileInterface::STATUS . ' = ?', Status::ACTIVE)
            ->where(ProfileInterface::IS_VIRTUAL . ' = ?', 0)
            ->where($this->getMainTable() . '.' . ProfileInterface::STORE_ID . ' = ?', $storeId)
            ->where(ScheduledPaymentInfoInterface::PAYMENT_STATUS . ' = ?', PaymentInterface::TYPE_PLANNED)
            ->where(
                'aw_sarp2_core_schedule_item.type IN (?)',
                [
                    PaymentInterface::TYPE_ACTUAL, PaymentInterface::TYPE_PLANNED
                ]
            )->order('aw_sarp2_core_schedule_item.scheduled_at ASC');

        return (int)$connection->fetchOne($select);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->joinLeft(
            ['customer_entity_table' => $this->getTable('customer_entity')],
            $this->getMainTable() . '.customer_id = customer_entity_table.entity_id',
            ['group_id']
        )->columns(
            [
                'customer_group_id' => $this->getConnection()->getIfNullSql(
                    'customer_entity_table.group_id',
                    new \Zend_Db_Expr('0')
                )
            ]
        );

        return $select;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var ProfileInterface|AbstractModel $object */
        if (!$object->getProfileId()) {
            $object->setStatus(Status::PENDING);
            $this->saveDefinition($object);
        }
        if (!$object->getHash()) {
            $object->setHash($this->hashGenerator->generate($object->getProfileId()));
        }
        if (!$object->getIncrementId()) {
            $store = $this->storeManager->getStore($object->getStoreId());
            $group = $this->storeManager->getGroup($store->getStoreGroupId());
            $sequence = $this->sequenceManager->getSequence(
                ProfileModel::ENTITY,
                $group->getDefaultStoreId()
            );
            $object->setIncrementId($sequence->getNextValue());
        }
        if ($object->getProfileId()
            && $object->getPlanId() != $object->getOrigData(ProfileInterface::PLAN_ID)
            && $object->getPlanDefinitionId() != $object->getOrigData(ProfileInterface::PLAN_DEFINITION_ID)
        ) {
            $this->removeDefinition($object->getProfileDefinitionId());
            $this->saveDefinition($object);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Remove profile definition id
     *
     * @param int $definitionId
     */
    private function removeDefinition($definitionId)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_sarp2_profile_definition');

        $connection->delete($table, ['definition_id = ?' => $definitionId]);
    }

    /**
     * Save definition
     *
     * @param ProfileInterface $profile
     */
    private function saveDefinition(ProfileInterface $profile)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('aw_sarp2_profile_definition');
        $definition = $profile->getPlanDefinition();
        $definitionData = $this->dataObjectProcessor->buildOutputDataArray(
            $definition,
            PlanDefinitionInterface::class
        );
        unset($definitionData[PlanDefinitionInterface::DEFINITION_ID]);

        $connection->insert($table, $definitionData);
        $profile->setProfileDefinitionId($connection->lastInsertId($table));
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var ProfileInterface|AbstractModel $object */
        foreach ($this->saveHandlers as $handler) {
            $handler->process($object);
        }
        return parent::_afterSave($object);
    }

    /**
     * Load plan definition
     *
     * @param int $definitionId
     * @param bool $isPlan
     * @return PlanDefinitionInterface
     */
    public function loadDefinition($definitionId, $isPlan = true)
    {
        return $this->definitionLoader->loadDefinition($definitionId, $isPlan);
    }
}
