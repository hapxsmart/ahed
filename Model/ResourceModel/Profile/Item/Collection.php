<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Item;

use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Item;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item as ItemResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile\Item
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
        $this->_init(Item::class, ItemResource::class);
    }

    /**
     * Add profile filter
     *
     * @param Profile $profile
     * @return $this
     */
    public function addProfileFilter(Profile $profile)
    {
        $profileId = $profile->getProfileId();
        if ($profileId) {
            $this->addFieldToFilter('profile_id', ['eq' => $profileId]);
        } else {
            $this->_totalRecords = 0;
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $resource = $this->getResource();
        /** @var Item $item */
        foreach ($this as $item) {
            $parentItemId = $item->getParentItemId();
            if ($parentItemId) {
                $item->setParentItem($this->getItemById($parentItemId));
            }
            $resource->unserializeFields($item);
        }
        $this->resetItemsDataChanged();
        return $this;
    }
}
