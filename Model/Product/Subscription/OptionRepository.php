<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterfaceFactory;
use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OptionRepository
 * @package Aheadworks\Sarp2\Model\Product\Subscription
 */
class OptionRepository implements SubscriptionOptionRepositoryInterface
{
    /**
     * @var OptionResource
     */
    private $resource;

    /**
     * @var SubscriptionOptionInterfaceFactory
     */
    private $optionFactory;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $instancesById = [];

    /**
     * @var array
     */
    private $instancesCache = [];

    /**
     * @param OptionResource $resource
     * @param SubscriptionOptionInterfaceFactory $optionFactory
     * @param CollectionFactory $collectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OptionResource $resource,
        SubscriptionOptionInterfaceFactory $optionFactory,
        CollectionFactory $collectionFactory,
        DataObjectHelper $dataObjectHelper,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->optionFactory = $optionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function save(SubscriptionOptionInterface $option)
    {
        try {
            $this->resource->save($option);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $option;
    }

    /**
     * {@inheritdoc}
     */
    public function get($optionId)
    {
        if (!isset($this->instancesById[$optionId])) {
            /** @var SubscriptionOptionInterface $option */
            $option = $this->optionFactory->create();
            $this->resource->load($option, $optionId);
            if (!$option->getOptionId()) {
                throw NoSuchEntityException::singleField('optionId', $optionId);
            }
            $this->instancesById[$optionId] = $option;
        }
        return $this->instancesById[$optionId];
    }

    /**
     * {@inheritdoc}
     */
    public function getList($productId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $key = $productId . '-' . $storeId;
        if (!isset($this->instancesCache[$key])) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();

            $this->extensionAttributesJoinProcessor->process($collection, SubscriptionOptionInterface::class);
            $collection
                ->addProductFilter($productId)
                ->addStoreFilter($storeId);

            $this->instancesCache[$key] = [];
            /** @var Option $optionModel */
            foreach ($collection as $optionModel) {
                /** @var SubscriptionOptionInterface $option */
                $option = $this->optionFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $option,
                    $optionModel->getData(),
                    SubscriptionOptionInterface::class
                );
                $this->instancesCache[$key][] = $option;
            }
        }
        return $this->instancesCache[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($optionId)
    {
        /** @var SubscriptionOptionInterface $option */
        $option = $this->optionFactory->create();
        $this->resource->load($option, $optionId);
        if (!$option->getOptionId()) {
            throw NoSuchEntityException::singleField('optionId', $optionId);
        }
        try {
            $this->resource->delete($option);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the subscription option: %1', $exception->getMessage())
            );
        }
        return true;
    }
}
