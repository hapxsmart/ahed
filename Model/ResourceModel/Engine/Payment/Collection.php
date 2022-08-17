<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment as PaymentResource;
use Aheadworks\Sarp2\Engine\Payment;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'item_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Payment::class, PaymentResource::class);
        $this->_map['fields']['profile_id'] = 'schedule_table.profile_id';
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(
                ['schedule_table' => $this->getTable('aw_sarp2_core_schedule')],
                'main_table.schedule_id = schedule_table.schedule_id',
                ['profile_id', 'store_id', 'payment_data']
            );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        foreach ($this as $item) {
            $this->getResource()->unserializeFields($item);
        }
        return parent::_afterLoad();
    }

    /**
     * Add type to status map filter to collection
     *
     * @param array $typeStatusMap
     * @return $this
     */
    public function addTypeStatusMapFilter($typeStatusMap)
    {
        $conditions = [];
        $connection = $this->getConnection();
        foreach ($typeStatusMap as $type => $statuses) {
            $conditions[] = $connection->quoteInto('type = ?', $type) . ' AND '
                . $connection->quoteInto('payment_status IN (?)', $statuses);
        }
        $this->getSelect()
            ->where('(' . implode(') ' . Select::SQL_OR . ' (', $conditions) . ')');
        return $this;
    }
}
