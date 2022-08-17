<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification as NotificationResource;
use Aheadworks\Sarp2\Engine\Notification;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'notification_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Notification::class, NotificationResource::class);
        $this->_map['fields']['status'] = 'main_table.status';
        $this->_map['fields']['profile_id'] = 'main_table.profile_id';
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(
                ['profile_table' => $this->getTable('aw_sarp2_profile')],
                'main_table.profile_id = profile_table.profile_id',
                ['profile_status' => 'status']
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
}
