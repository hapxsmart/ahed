<?php
namespace Aheadworks\Sarp2\Model\Plan;

use Aheadworks\Sarp2\Model\Plan;
use Aheadworks\Sarp2\Model\Plan\DataProvider\Processor\Composite as DataProviderProcessor;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class DataProvider
 * @package Aheadworks\Sarp2\Model\Plan
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataProviderProcessor
     */
    private $dataProcessor;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $subscriptionPlanCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param DataProviderProcessor $dataProcessor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $subscriptionPlanCollectionFactory,
        DataPersistorInterface $dataPersistor,
        DataProviderProcessor $dataProcessor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $subscriptionPlanCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->dataProcessor = $dataProcessor;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var Plan $plan */
        foreach ($items as $plan) {
            $this->loadedData[$plan->getPlanId()] = $this->dataProcessor->prepareData(
                $plan->getData()
            );
        }

        $data = $this->dataPersistor->get('aw_sarp2_plan');
        if (!empty($data)) {
            $plan = $this->collection->getNewEmptyItem();
            $plan->setData($data);
            $this->loadedData[$plan->getPlanId()] = $this->dataProcessor->prepareData(
                $plan->getData()
            );
            $this->dataPersistor->clear('aw_sarp2_plan');
        }

        if (empty($this->loadedData)) {
            $plan = $this->collection->getNewEmptyItem();
            $this->loadedData[$plan->getPlanId()] = $this->dataProcessor->prepareData(
                $plan->getData()
            );
        }

        return $this->loadedData;
    }
}
