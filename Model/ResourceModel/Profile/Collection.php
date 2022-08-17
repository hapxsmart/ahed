<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface as ItemInterface;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'profile_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Profile::class, ProfileResource::class);
        $this->_map['fields']['store_id'] = 'main_table.store_id';
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (in_array($field, [ItemInterface::PRODUCT_ID])) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            'aw_sarp2_profile_item',
            ProfileInterface::PROFILE_ID,
            ItemInterface::PROFILE_ID,
            ItemInterface::PRODUCT_ID,
            ItemInterface::PRODUCT_ID
        );
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachItems();
        return parent::_afterLoad();
    }

    /**
     * Attach profile items data to collection
     *
     * @return void
     */
    private function attachItems()
    {
        $profileIds = $this->getColumnValues('profile_id');
        if (count($profileIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['profile_item_table' => $this->getTable('aw_sarp2_profile_item')])
                ->where('profile_item_table.' . ItemInterface::PROFILE_ID . ' IN (?)', $profileIds)
                ->where('profile_item_table.' . ItemInterface::PARENT_ITEM_ID . ' IS NULL')
                ->where('profile_item_table.' . ItemInterface::REPLACEMENT_ITEM_ID . ' IS NULL')
                ->order('profile_item_table.' . ItemInterface::NAME . ' ' . self::SORT_ORDER_ASC);
            $itemsData = $connection->fetchAll($select);

            /** @var \Magento\Framework\DataObject $profile */
            foreach ($this as $profile) {
                $profileId = $profile->getData('profile_id');
                $items = [];
                foreach ($itemsData as $data) {
                    if ($data['profile_id'] == $profileId) {
                        $items[] = $data;
                    }
                }
                $profile->setData('items', $items);
            }
        }
    }
}
