<?php
namespace Aheadworks\Sarp2\Model\Access;

use Aheadworks\Sarp2\Api\AccessTokenRepositoryInterface;
use Aheadworks\Sarp2\Api\Data\AccessTokenInterface;
use Aheadworks\Sarp2\Api\Data\AccessTokenInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\AccessTokenSearchResultsInterface;
use Aheadworks\Sarp2\Api\Data\AccessTokenSearchResultsInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Access\Token as TokenResource;
use Aheadworks\Sarp2\Model\ResourceModel\Access\Token\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Access\Token\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class TokenRepository
 *
 * @package Aheadworks\Sarp2\Model\Access
 */
class TokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var AccessTokenInterface[]
     */
    private $instances = [];

    /**
     * @var AccessTokenInterface[]
     */
    private $instancesByValue = [];

    /**
     * @var TokenResource
     */
    private $resource;

    /**
     * @var AccessTokenInterfaceFactory
     */
    private $tokenFactory;

    /**
     * @var AccessTokenSearchResultsInterfaceFactory
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
     * @param AccessTokenInterfaceFactory $tokenFactory
     * @param AccessTokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        TokenResource $resource,
        AccessTokenInterfaceFactory $tokenFactory,
        AccessTokenSearchResultsInterfaceFactory $searchResultsFactory,
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
    public function get($tokenId)
    {
        if (!isset($this->instances[$tokenId])) {
            /** @var AccessTokenInterface $token */
            $token = $this->tokenFactory->create();
            $this->resource->load($token, $tokenId);
            if (!$token->getId()) {
                throw NoSuchEntityException::singleField('tokenId', $tokenId);
            }
            $this->instances[$tokenId] = $token;
            $this->instancesByValue[$token->getTokenValue()] = $token;
        }
        return $this->instances[$tokenId];
    }

    /**
     * {@inheritdoc}
     */
    public function getByValue($tokenValue)
    {
        if (!isset($this->instancesByValue[$tokenValue])) {
            /** @var AccessTokenInterface $token */
            $token = $this->tokenFactory->create();
            $this->resource->load($token, $tokenValue, AccessTokenInterface::TOKEN_VALUE);
            if (!$token->getId()) {
                throw NoSuchEntityException::singleField('token', $tokenValue);
            }
            $this->instancesByValue[$tokenValue] = $token;
            $this->instances[$token->getId()] = $token;
        }
        return $this->instancesByValue[$tokenValue];
    }

    /**
     * {@inheritdoc}
     */
    public function save(AccessTokenInterface $token)
    {
        try {
            $this->resource->save($token);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $tokenId = $token->getId();
        unset($this->instances[$tokenId]);
        return $this->get($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var AccessTokenSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create()
            ->setSearchCriteria($searchCriteria);
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->extensionAttributesJoinProcessor->process($collection, AccessTokenInterface::class);
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
            /** @var AccessTokenInterface $token */
            $token = $this->tokenFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $token,
                $tokenModel->getData(),
                AccessTokenInterface::class
            );
            $tokens[] = $token;
        }

        $searchResults
            ->setSearchCriteria($searchCriteria)
            ->setItems($tokens)
            ->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($token)
    {
        try {
            $this->resource->delete($token);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        if (isset($this->instances[$token->getId()])) {
            unset($this->instances[$token->getId()]);
        }
        if (isset($this->instancesByValue[$token->getTokenValue()])) {
            unset($this->instancesByValue[$token->getTokenValue()]);
        }

        return true;
    }
}
