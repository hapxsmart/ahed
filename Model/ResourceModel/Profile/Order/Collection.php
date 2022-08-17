<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Order;

use Aheadworks\Sarp2\Model\Profile\Order as ProfileOrder;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order as ProfileOrderResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile\Order
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ProfileOrder::class, ProfileOrderResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['sales_order_table' => $this->getTable('sales_order')],
            'sales_order_table.entity_id = main_table.order_id',
            [
                'order_increment_id' => 'sales_order_table.increment_id',
                'order_date' => 'sales_order_table.created_at',
                'base_grand_total' => 'sales_order_table.base_grand_total',
                'grand_total' => 'sales_order_table.grand_total',
                'base_currency_code' => 'sales_order_table.base_currency_code',
                'order_currency_code' => 'sales_order_table.order_currency_code',
                'order_status' => 'sales_order_table.status'
            ]
        );
        return $this;
    }
}
