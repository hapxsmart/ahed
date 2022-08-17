<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile as ProfileResource;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\CollectionFactory;
use Aheadworks\Sarp2\Model\Sales\Total\Profile\CollectorList;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProfileRepository implements ProfileRepositoryInterface
{
    /**
     * @var ProfileInterface[]
     */
    private $instances = [];

    /**
     * @var ProfileResource
     */
    private $resource;

    /**
     * @var ProfileInterfaceFactory
     */
    private $profileFactory;

    /**
     * @var CollectorList
     */
    private $totalCollectorList;

    /**
     * @var ProfileSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var JoinProcessorInterface
     */
    private $extensionAttributesJoinProcessor;

    /**
     * @param ProfileResource $resource
     * @param ProfileInterfaceFactory $profileFactory
     * @param CollectorList $totalCollectorList
     * @param ProfileSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ProfileResource $resource,
        ProfileInterfaceFactory $profileFactory,
        CollectorList $totalCollectorList,
        ProfileSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
        $this->totalCollectorList = $totalCollectorList;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ProfileInterface $profile, $recollectTotals = true)
    {
        if ($recollectTotals) {
            foreach ($this->totalCollectorList->getCollectors() as $collector) {
                $collector->collect($profile);
            }
        }
        try {
            $this->resource->save($profile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $profileId = $profile->getProfileId();
        unset($this->instances[$profileId]);
        return $this->get($profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($profileId)
    {
        if (!isset($this->instances[$profileId])) {
            /** @var ProfileInterface $profile */
            $profile = $this->profileFactory->create();
            $this->resource->load($profile, $profileId);
            if (!$profile->getProfileId()) {
                throw NoSuchEntityException::singleField('profileId', $profileId);
            }
            $this->instances[$profileId] = $profile;
        }
        return $this->instances[$profileId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByHash($hash)
    {
        /** @var ProfileInterface|AbstractModel $profile */
        $profile = $this->profileFactory->create();
        $this->resource->load($profile, $hash, ProfileInterface::HASH);
        if (!$profile->getProfileId()) {
            throw NoSuchEntityException::singleField(ProfileInterface::HASH, $hash);
        }

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ProfileSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, ProfileInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType()
                    ? $filter->getConditionType()
                    : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $collection
            ->setCurPage($searchCriteria->getCurrentPage())
            ->setPageSize($searchCriteria->getPageSize());

        $profiles = [];
        /** @var Profile $profileModel */
        foreach ($collection as $profileModel) {
            /** @var ProfileInterface $profile */
            $profile = $this->profileFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $profile,
                $profileModel->getData(),
                ProfileInterface::class
            );
            $profiles[] = $profile;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($profiles)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
