<?php
namespace Aheadworks\Sarp2\Model\Payment;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\PaymentTokenSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\PaymentTokenSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Api\PaymentTokenRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Token\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TokenRepository
 * @package Aheadworks\Sarp2\Model\Payment
 */
class TokenRepository implements PaymentTokenRepositoryInterface
{
    /**
     * @var PaymentTokenInterface[]
     */
    private $instances = [];

    /**
     * @var TokenResource
     */
    private $resource;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var PaymentTokenSearchResultsInterfaceFactory
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
     * @param TokenResource $resource
     * @param PaymentTokenInterfaceFactory $tokenFactory
     * @param PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        TokenResource $resource,
        PaymentTokenInterfaceFactory $tokenFactory,
        PaymentTokenSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->tokenFactory = $tokenFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(PaymentTokenInterface $token)
    {
        try {
            $this->resource->save($token);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $tokenId = $token->getTokenId();
        unset($this->instances[$tokenId]);
        return $this->get($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function get($tokenId)
    {
        if (!isset($this->instances[$tokenId])) {
            /** @var PaymentTokenInterface $token */
            $token = $this->tokenFactory->create();
            $this->resource->load($token, $tokenId);
            if (!$token->getTokenId()) {
                throw NoSuchEntityException::singleField('tokenId', $tokenId);
            }
            $this->instances[$tokenId] = $token;
        }
        return $this->instances[$tokenId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var PaymentTokenSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, PaymentTokenInterface::class);
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
        if ($sortOrders = $searchCriteria->getSortOrders()) {
            /** @var \Magento\Framework\Api\SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder($sortOrder->getField(), $sortOrder->getDirection());
            }
        }

        $tokens = [];
        /** @var Token $tokenModel */
        foreach ($collection as $tokenModel) {
            /** @var PaymentTokenInterface $token */
            $token = $this->tokenFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $token,
                $tokenModel->getData(),
                PaymentTokenInterface::class
            );
            $tokens[] = $token;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($tokens)
            ->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
