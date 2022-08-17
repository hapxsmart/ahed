<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment as PaymentModel;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Entity\DataPreparer;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler\OrderHandler;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Payment
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine
 */
class Payment extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataPreparer
     */
    private $dataPreparer;

    /**
     * @var OrderHandler
     */
    private $orderHandler;

    /**
     * {@inheritdoc}
     */
    protected $_serializableFields = ['payment_data' => [null, []]];

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param DataPreparer $dataPreparer
     * @param OrderHandler $orderHandler
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        DataPreparer $dataPreparer,
        OrderHandler $orderHandler,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->dataPreparer = $dataPreparer;
        $this->orderHandler = $orderHandler;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_core_schedule_item', 'item_id');
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
            ['schedule_table' => $this->getTable('aw_sarp2_core_schedule')],
            $this->getMainTable() . '.schedule_id = schedule_table.schedule_id',
            ['profile_id', 'store_id', 'payment_data']
        );
        return $select;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var PaymentModel $object */
        $this->saveSchedule($object);
        $this->saveParent($object);
        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var PaymentModel $object */
        $this->updateSchedule($object);
        $this->updateProfile($object);
        return parent::_afterSave($object);
    }

    /**
     * Mass payments update
     *
     * @param PaymentModel[] $payments
     * @return $this
     * @throws \Exception
     */
    public function massUpdate($payments)
    {
        $this->beginTransaction();
        try {
            foreach ($payments as $payment) {
                $this->updateObject($payment);
                $this->_afterSave($payment);
            }
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Mass payments delete
     *
     * @param PaymentModel[] $payments
     * @param bool $withSchedule
     * @return $this
     * @throws \Exception
     */
    public function massDelete($payments, $withSchedule = true)
    {
        $connection = $this->transactionManager->start($this->getConnection());
        try {
            foreach ($payments as $payment) {
                $this->objectRelationProcessor->delete(
                    $this->transactionManager,
                    $connection,
                    $this->getMainTable(),
                    $this->getConnection()->quoteInto($this->getIdFieldName() . ' = ? ', $payment->getId()),
                    $payment->getData()
                );
                if ($withSchedule) {
                    $this->deleteSchedule($payment);
                }
            }
            $this->transactionManager->commit();
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Change payments statuses
     *
     * @param array $paymentIds
     * @param string $status
     * @return $this
     */
    public function changeStatus($paymentIds, $status)
    {
        $table = $this->getTable('aw_sarp2_core_schedule_item');
        $connection = $this->getConnection();
        $connection->update(
            $table,
            ['payment_status' => $status],
            ['item_id IN(?)' => $paymentIds]
        );
        return $this;
    }

    /**
     * Change payments type
     *
     * @param array $paymentIds
     * @param string $type
     * @return $this
     */
    public function changeType($paymentIds, $type)
    {
        $table = $this->getTable('aw_sarp2_core_schedule_item');
        $connection = $this->getConnection();
        $connection->update(
            $table,
            ['type' => $type],
            ['item_id IN(?)' => $paymentIds]
        );
        return $this;
    }

    /**
     * Change payments status and type
     *
     * @param array $paymentIds
     * @param string $status
     * @param string $type
     * @return $this
     */
    public function changeStatusAndType($paymentIds, $status, $type)
    {
        $table = $this->getTable('aw_sarp2_core_schedule_item');
        $connection = $this->getConnection();
        $connection->update(
            $table,
            [
                'payment_status' => $status,
                'type' => $type
            ],
            ['item_id IN(?)' => $paymentIds]
        );
        return $this;
    }

    /**
     * Save schedule data
     *
     * @param PaymentModel $payment
     * @return void
     */
    private function saveSchedule($payment)
    {
        $schedule = $payment->getSchedule();
        if ($schedule) {
            $scheduleId = $schedule->getScheduleId();
            if (!$scheduleId) {
                $connection = $this->getConnection();
                $table = $this->getTable('aw_sarp2_core_schedule');
                $data = $this->getScheduleDataForTable(
                    $schedule,
                    $payment,
                    $table
                );
                $connection->insert($table, $data);
                $payment->setScheduleId($connection->lastInsertId($table));
            }
        }
    }

    /**
     * Update schedule data
     *
     * @param PaymentModel $payment
     * @return void
     */
    private function updateSchedule($payment)
    {
        /** @var Schedule $schedule */
        $schedule = $payment->getSchedule();
        if ($schedule) {
            $scheduleId = $schedule->getScheduleId();
            if ($scheduleId) {
                $connection = $this->getConnection();
                $table = $this->getTable('aw_sarp2_core_schedule');
                $data = $this->getScheduleDataForTable(
                    $schedule,
                    $payment,
                    $table
                );
                $connection->update($table, $data, ['schedule_id = ?' => $scheduleId]);
            }
        }
    }

    /**
     * Delete payment schedule
     *
     * @param PaymentModel $payment
     * @return void
     */
    private function deleteSchedule($payment)
    {
        $scheduleId = $payment->getScheduleId();
        if ($scheduleId) {
            $connection = $this->getConnection();
            $table = $this->getTable('aw_sarp2_core_schedule');
            $select = $connection->select()
                ->from($table)
                ->where('schedule_id = ?', $scheduleId);
            if ($connection->fetchOne($select)) {
                $connection->delete($table, $connection->quoteInto('schedule_id = ?', $scheduleId));
            }
        }
    }

    /**
     * Save parent payment data
     *
     * @param PaymentModel $payment
     * @return void
     */
    private function saveParent($payment)
    {
        $parentItem = $payment->getParentItem();
        if ($parentItem) {
            $parentItemId = $parentItem->getId();
            if (!$parentItemId) {
                $this->save($parentItem);
                $payment->setParentId($parentItem->getId());
            }
        }
    }

    /**
     * Get schedule data for table
     *
     * @param Schedule $schedule
     * @param PaymentModel $payment
     * @param string $table
     * @return array
     */
    private function getScheduleDataForTable($schedule, $payment, $table)
    {
        $data = [];
        $sources = [$schedule, $payment];

        foreach ($sources as $object) {
            if (is_array($object->getPaymentData())) {
                $this->_serializeField($object, 'payment_data');
            }
            $data = array_merge($data, $this->_prepareDataForTable($object, $table));
        }

        return $data;
    }

    /**
     * Update profile data
     *
     * @param PaymentModel $payment
     * @return void
     */
    private function updateProfile($payment)
    {
        $profile = $payment->getProfile();
        $profileId = $payment->getProfileId();
        if ($profile && $profileId) {
            $table = $this->getTable('aw_sarp2_profile');
            $connection = $this->getConnection();

            $data = $this->_prepareDataForTable(
                $this->dataPreparer->prepareForUpdate($profile, ProfileInterface::class),
                $table
            );
            $connection->update($table, $data, ['profile_id = ?' => $profileId]);

            $this->orderHandler->process($profile);
        }
    }
}
