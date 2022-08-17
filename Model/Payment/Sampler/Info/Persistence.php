<?php
namespace Aheadworks\Sarp2\Model\Payment\Sampler\Info;

use Aheadworks\Sarp2\Model\Payment\Sampler\Info;
use Aheadworks\Sarp2\Model\Payment\Sampler\InfoFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info as InfoResource;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Payment\Sampler\Info\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Persistence
 * @package Aheadworks\Sarp2\Model\Payment\Sampler\Info
 */
class Persistence
{
    /**
     * @var InfoResource
     */
    private $resource;

    /**
     * @var InfoFactory
     */
    private $samplerInfoFactory;

    /**
     * @var CollectionFactory
     */
    private $samplerInfoCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @param InfoResource $resource
     * @param InfoFactory $samplerInfoFactory
     * @param CollectionFactory $samplerInfoCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        InfoResource $resource,
        InfoFactory $samplerInfoFactory,
        CollectionFactory $samplerInfoCollectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->samplerInfoFactory = $samplerInfoFactory;
        $this->samplerInfoCollectionFactory = $samplerInfoCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Retrieve sampler info by id
     *
     * @param int $samplerId
     * @return Info
     * @throws NoSuchEntityException
     */
    public function get($samplerId)
    {
        /** @var Info $info */
        $info = $this->samplerInfoFactory->create();
        $this->resource->load($info, $samplerId);
        if (!$info->getId()) {
            throw NoSuchEntityException::singleField('id', $samplerId);
        }
        return $info;
    }

    /**
     * Save sampler info instance
     *
     * @param Info $info
     * @return Info
     * @throws CouldNotSaveException
     */
    public function save(Info $info)
    {
        try {
            $this->resource->save($info);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $info;
    }

    /**
     * Delete sampler info
     *
     * @param Info $info
     * @return void
     * @throws CouldNotDeleteException
     */
    public function delete(Info $info)
    {
        try {
            $this->resource->delete($info);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
    }

    /**
     * Retrieve sampler info objects matching the specified criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return Info[]
     * @throws NoSuchEntityException
     */
    public function getList($searchCriteria)
    {
        /** @var Collection $collection */
        $collection = $this->samplerInfoCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $infoArray = [];
        foreach ($collection as $samplerInfoObject) {
            $infoArray[] = $this->get($samplerInfoObject->getId());
        }

        return $infoArray;
    }
}
