<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Order
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class Order extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_profile_order', 'id');
    }
}
