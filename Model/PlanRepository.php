<?php
namespace Aheadworks\Sarp2\Model;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\PlanSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\PlanSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PlanRepository
 * @package Aheadworks\Sarp2\Model
 */
class PlanRepository implements PlanRepositoryInterface
{
    /**
     * @var PlanInterface[]
     */
    private $instances = [];

    /**
     * @var PlanResource
     */
    private $resource;

    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var PlanSearchResultsInterfaceFactory
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
     * @param PlanResource $resource
     * @param PlanInterfaceFactory $planFactory
     * @param PlanSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        PlanResource $resource,
        PlanInterfaceFactory $planFactory,
        PlanSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->planFactory = $planFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(PlanInterface $plan)
    {
        try {
            $this->resource->save($plan);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $planId = $plan->getPlanId();
        unset($this->instances[$planId]);
        return $this->get($planId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($planId)
    {
        if (!isset($this->instances[$planId])) {
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->resource->load($plan, $planId);
            if (!$plan->getPlanId()) {
                throw NoSuchEntityException::singleField('planId', $planId);
            }
            $this->instances[$planId] = $plan;
        }
        return $this->instances[$planId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var PlanSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, PlanInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType()
                    ? $filter->getConditionType()
                    : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $plans = [];
        /** @var Plan $planModel */
        foreach ($collection as $planModel) {
            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $plan,
                $planModel->getData(),
                PlanInterface::class
            );
            $plans[] = $plan;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($plans)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PlanInterface $plan)
    {
        return $this->deleteById($plan->getPlanId());
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($planId)
    {
        /** @var PlanInterface $plan */
        $plan = $this->planFactory->create();
        $this->resource->load($plan, $planId);
        if (!$plan->getPlanId()) {
            throw NoSuchEntityException::singleField('planId', $planId);
        }
        try {
            $this->resource->delete($plan);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete the plan: %1', $exception->getMessage()));
        }
        unset($this->instances[$planId]);
        return true;
    }
}
