<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class OrderRepository
 * @package Aheadworks\Sarp2\Model\Profile
 */
class OrderRepository implements ProfileOrderRepositoryInterface
{
    /**
     * @var ProfileOrderInterfaceFactory
     */
    private $profileOrderFactory;

    /**
     * @var ProfileOrderSearchResultsInterfaceFactory
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
     * @param ProfileOrderInterfaceFactory $profileOrderFactory
     * @param ProfileOrderSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ProfileOrderInterfaceFactory $profileOrderFactory,
        ProfileOrderSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->profileOrderFactory = $profileOrderFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var ProfileOrderSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, ProfileOrderInterface::class);
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
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

        $profileOrders = [];
        /** @var \Aheadworks\Sarp2\Model\Profile\Order $profileOrderModel */
        foreach ($collection as $profileOrderModel) {
            /** @var ProfileOrderInterface $profileOrder */
            $profileOrder = $this->profileOrderFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $profileOrder,
                $profileOrderModel->getData(),
                ProfileOrderInterface::class
            );
            $profileOrders[] = $profileOrder;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($profileOrders)
            ->setTotalCount($collection->getSize());
        return $searchResults;
    }
}
