<?php
namespace Aheadworks\Sarp2\Model\Profile\Item;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item\Collection as ItemCollection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item\CollectionFactory as ItemCollectionFactory;

/**
 * Class Finder
 *
 * @package Aheadworks\Sarp2\Model\Profile\Item
 */
class Finder
{
    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @param ItemCollectionFactory $itemCollectionFactory
     */
    public function __construct(ItemCollectionFactory $itemCollectionFactory)
    {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * Retrieve profile items without replaced items (change for next only)
     *
     * @param $profile
     * @return ProfileItemInterface[]|\Magento\Framework\DataObject[]
     */
    public function getItemsWithoutHiddenReplaced($profile)
    {
        /** @var ItemCollection $collection */
        $collection = $this->itemCollectionFactory->create();
        $collection
            ->addProfileFilter($profile)
            ->addFieldToFilter(ProfileItemInterface::REPLACEMENT_ITEM_ID, ['null' => true])
            ->setOrder(ProfileItemInterface::NAME, $collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }

    /**
     * Retrieve all profile items
     *
     * @param $profile
     * @return ProfileItemInterface[]|\Magento\Framework\DataObject[]
     */
    public function getItemsWithHiddenReplaced($profile)
    {
        /** @var ItemCollection $collection */
        $collection = $this->itemCollectionFactory->create();
        $collection
            ->addProfileFilter($profile)
            ->setOrder(ProfileItemInterface::NAME, $collection::SORT_ORDER_ASC);
        return $collection->getItems();
    }
}
