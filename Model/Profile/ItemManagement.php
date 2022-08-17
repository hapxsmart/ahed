<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Item as ProfileItem;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsOneOffItem;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ItemManagement
 *
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ItemManagement
{
    /**
     * @var ProfileItemRepositoryInterface
     */
    private $profileItemRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var IsOneOffItem
     */
    private $isOneOffItem;

    /**
     * @param ProfileItemRepositoryInterface $itemRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param IsOneOffItem $isOneOffItem
     */
    public function __construct(
        ProfileItemRepositoryInterface $itemRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        IsOneOffItem $isOneOffItem
    ) {
        $this->profileItemRepository = $itemRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->isOneOffItem = $isOneOffItem;
    }

    /**
     * Retrieve profile item by id from profile
     *
     * @param int $itemId
     * @param ProfileInterface $profile
     * @return ProfileItemInterface|ProfileItem|null
     */
    public function getItemFromProfileById($itemId, ProfileInterface $profile)
    {
        foreach ($profile->getItems() as $item) {
            if ($item->getItemId() == $itemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Save profile item
     *
     * @param ProfileItemInterface $item
     * @return ProfileItemInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveItem(ProfileItemInterface $item)
    {
        return $this->profileItemRepository->save($item);
    }

    /**
     * Add item into profile
     *
     * @param ProfileItemInterface $item
     * @param ProfileInterface $profile
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addItemToProfile(ProfileItemInterface $item, ProfileInterface $profile)
    {
        $item->setProfileId($profile->getProfileId());
        $this->profileItemRepository->save($item);

        $this->insertToProfileItemsArray($item, $profile);

        return $this;
    }

    /**
     * Delete profile item
     *
     * @param ProfileItemInterface $item
     * @return $this
     * @throws LocalizedException
     */
    public function deleteItem(ProfileItemInterface $item)
    {
        $this->profileItemRepository->delete($item);

        return $this;
    }

    /**
     * Delete item from profile
     *
     * @param ProfileItemInterface $item
     * @param ProfileInterface $profile
     * @return $this
     * @throws LocalizedException
     */
    public function deleteItemFromProfile(ProfileItemInterface $item, ProfileInterface $profile)
    {
        $wasUnsetItems = $this->removeFromProfileItemsArray($item, $profile);
        $wasDeletedItems = [];
        foreach ($wasUnsetItems as $wasUnsetItem) {
            if (in_array($wasUnsetItem->getParentItemId(), $wasDeletedItems)) {
                continue;
            }
            $this->profileItemRepository->delete($wasUnsetItem);
            $wasDeletedItems[] = $wasUnsetItem->getItemId();
        }

        return $this;
    }

    /**
     * Replace item with another item
     *
     * @param ProfileItemInterface $item
     * @param ProfileItemInterface $replaceToItem
     * @param ProfileInterface $profile
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function replaceWithAnotherItem(
        ProfileItemInterface $item,
        ProfileItemInterface $replaceToItem,
        ProfileInterface $profile
    ) {
        if ($originalItem = $this->getOriginalItemForReplacementItem($item)) {
            $this->deleteItemFromProfile($item, $profile);
            $this->setReplacementItemId($originalItem, $replaceToItem->getItemId());
        } else {
            $this->removeFromProfileItemsArray($item, $profile);
            $this->setReplacementItemId($item, $replaceToItem->getItemId());
        }

        return $this;
    }

    /**
     * Retrieve original item from replacement item
     *
     * @param ProfileItemInterface $replacementItem
     * @return ProfileItemInterface|mixed|null
     * @throws LocalizedException
     */
    public function getOriginalItemForReplacementItem(ProfileItemInterface $replacementItem)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileItemInterface::REPLACEMENT_ITEM_ID, $replacementItem->getItemId());

        $result = $this->profileItemRepository->getList($this->searchCriteriaBuilder->create());
        if ($result->getTotalCount() > 0) {
            $items = $result->getItems();
            return reset($items);
        }

        return null;
    }

    /**
     * Retrieve all replaced items for profile
     *
     * @param ProfileInterface $profile
     * @return ProfileItemInterface[]
     * @throws LocalizedException
     */
    public function getAllReplacedItemsForProfile(ProfileInterface $profile)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileItemInterface::PROFILE_ID, $profile->getProfileId())
            ->addFilter(ProfileItemInterface::REPLACEMENT_ITEM_ID, 'NULL', 'neq');

        $result = $this->profileItemRepository->getList($this->searchCriteriaBuilder->create());

        return $result->getItems();
    }

    /**
     * Reset replacement item to original items
     *
     * @param ProfileInterface $profile
     * @return bool
     * @throws LocalizedException
     */
    public function resetAllReplacementItems(ProfileInterface $profile)
    {
        $wasReset = false;
        $originalItems = $this->getAllReplacedItemsForProfile($profile);
        foreach ($originalItems as $originalItem) {
            $replacementItemId = $originalItem->getReplacementItemId();
            $this->setReplacementItemId($originalItem, null);
            $this->insertToProfileItemsArray($originalItem, $profile);
            try {
                $replacementItem = $this->profileItemRepository->get($replacementItemId);
                $this->deleteItemFromProfile($replacementItem, $profile);
            } catch (\Exception $exception) {
            }
            $wasReset = true;
        }

        return $wasReset;
    }

    /**
     * Delete one-off items
     *
     * @param ProfileInterface $profile
     * @throws LocalizedException
     */
    public function deleteOneOffItems(ProfileInterface $profile): void
    {
        $profileItems = $profile->getItems();
        foreach ($profileItems as $profileItem) {
            if ($this->isOneOffItem->check($profileItem)) {
                $this->deleteItemFromProfile($profileItem, $profile);
            }
        }
    }

    /**
     * Insert item to profile items array without save in database
     *
     * @param ProfileItemInterface $item
     * @param ProfileInterface $profile
     * @return ProfileItemInterface[]
     */
    private function insertToProfileItemsArray(ProfileItemInterface $item, ProfileInterface $profile)
    {
        $items = $profile->getItems();
        $items[$item->getItemId()] = $item;
        $profile->setItems($items);

        return $items;
    }

    /**
     * Remove item from profile items array without remove from database
     *
     * @param ProfileItemInterface $removedItem
     * @param ProfileInterface $profile
     * @return ProfileItemInterface[]
     */
    private function removeFromProfileItemsArray(ProfileItemInterface $removedItem, ProfileInterface $profile)
    {
        $profileItems = [];
        $wasUnsetItemsIds = $wasUnsetItems = [];
        foreach ($profile->getItems() as $profileItemId => $profileItem) {
            if ($profileItem->getItemId() == $removedItem->getItemId()) {
                $wasUnsetItems[] = $removedItem;
                $wasUnsetItemsIds[] = $removedItem->getItemId();
                continue;
            }
            if ($profileItem->getParentItemId() == $removedItem->getItemId()) {
                $wasUnsetItems[] = $profileItem;
                continue;
            }
            if (in_array($profileItem->getParentItemId(), $wasUnsetItemsIds)) {
                $wasUnsetItems[] = $profileItem;
                continue;
            }

            $profileItems[$profileItemId] = $profileItem;
        }

        $profile->setItems($profileItems);

        return $wasUnsetItems;
    }

    /**
     * Set replacement item id
     *
     * @param ProfileItem|ProfileItemInterface $item
     * @param int $replacementItemId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function setReplacementItemId($item, $replacementItemId)
    {
        $item->setReplacementItemId($replacementItemId);
        $this->profileItemRepository->save($item);
        foreach ($item->getChildItems() as $childItem) {
            $childItem->setReplacementItemId($replacementItemId);
            $this->profileItemRepository->save($childItem);
        }
    }
}
