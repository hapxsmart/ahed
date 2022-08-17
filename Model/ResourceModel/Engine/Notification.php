<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Notification
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine
 */
class Notification extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['notification_data' => [null, []]];

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
        $this->_init('aw_sarp2_core_notification', 'notification_id');
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
     * {@inheritdoc}
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            ['profile_table' => $this->getTable('aw_sarp2_profile')],
            $this->getMainTable() . '.profile_id = profile_table.profile_id',
            ['profile_status' => 'status']
        );
        return $select;
    }

    /**
     * Mass notifications delete
     *
     * @param \Aheadworks\Sarp2\Engine\Notification[] $notifications
     * @return $this
     * @throws \Exception
     */
    public function massDelete($notifications)
    {
        $connection = $this->transactionManager->start($this->getConnection());
        try {
            foreach ($notifications as $notification) {
                $this->objectRelationProcessor->delete(
                    $this->transactionManager,
                    $connection,
                    $this->getMainTable(),
                    $this->getConnection()->quoteInto($this->getIdFieldName() . ' = ? ', $notification->getId()),
                    $notification->getData()
                );
            }
            $this->transactionManager->commit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Clean notifications
     *
     * @param string $olderThanDate
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function cleanUpObsoleteMessages($olderThanDate)
    {
        $connection = $this->getConnection();

        $conditions = [
            $connection->prepareSqlCondition(
                'status',
                ['$in' => [
                    NotificationInterface::STATUS_SEND,
                    NotificationInterface::STATUS_DECLINED
                ]]
            ),
            $connection->prepareSqlCondition(
                'scheduled_at',
                ['lteq' => $olderThanDate]
            ),
        ];

        return $this->getConnection()->delete(
            $this->getMainTable(),
            implode(' AND ', $conditions)
        );
    }
}
