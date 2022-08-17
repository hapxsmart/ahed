<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileItemSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileItemRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item\CollectionFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Item as ItemResource;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ItemRepository
 * @package Aheadworks\Sarp2\Model\Profile
 */
class ItemRepository implements ProfileItemRepositoryInterface
{
    /**
     * @var ProfileItemInterface[]
     */
    private $instances = [];

    /**
     * @var ItemResource
     */
    private $resource;

    /**
     * @var ProfileItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * @var ProfileItemSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param ItemResource $resource
     * @param ProfileItemInterfaceFactory $itemFactory
     * @param ProfileItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ItemResource $resource,
        ProfileItemInterfaceFactory $itemFactory,
        ProfileItemSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProfileItemInterface $item)
    {
        try {
            if ($item->getParentItem()) {
                $item->setParentItemId($item->getParentItem()->getItemId());
            }
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $itemId = $item->getItemId();
        unset($this->instances[$itemId]);
        return $this->get($itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($itemId)
    {
        if (!isset($this->instances[$itemId])) {
            /** @var ProfileItemInterface $item */
            $item = $this->itemFactory->create();
            $this->resource->load($item, $itemId);
            if (!$item->getItemId()) {
                throw NoSuchEntityException::singleField('itemId', $itemId);
            }
            $this->instances[$itemId] = $item;
        }
        return $this->instances[$itemId];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        if (isset($this->instances[$item->getItemId()])) {
            unset($this->instances[$item->getItemId()]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, ProfileItemInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var ProfileItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
