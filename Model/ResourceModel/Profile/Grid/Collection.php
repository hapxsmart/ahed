<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid;

use Aheadworks\Sarp2\Model\ResourceModel\Profile\Collection as ProfileCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Aheadworks\Sarp2\Model\ResourceModel\Collection\ModifierInterface
    as CollectionModifierInterface;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid
 */
class Collection extends ProfileCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var bool
     */
    private $addOrderInfoFlag = false;

    /**
     * @var OrderCollection
     */
    private $orderCollection;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param CollectionModifierInterface $modifier
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param OrderCollection $orderCollection
     * @param string $model
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        CollectionModifierInterface $modifier,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        OrderCollection $orderCollection,
        $model = Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $modifier,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->orderCollection = $orderCollection;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $connection = $this->getResource()->getConnection();
        $this->_map['fields']['customer_email'] = 'main_table.customer_email';
        $this->_map['fields']['increment_id'] = 'main_table.increment_id';
        $this->_map['fields']['customer_group_id'] = $this->getGroupExpression();
        $this->_map['fields']['status'] = 'main_table.status';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
        $this->setAddOrderInfoFlag();
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(
                ['customer_entity_table' => $this->getTable('customer_entity')],
                'main_table.customer_id = customer_entity_table.entity_id',
                ['email', 'group_id']
            )->columns(
                ['customer_group_id' => $this->getGroupExpression()]
            );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->addOrderInfo();
        return parent::_afterLoad();
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Add order info on collection loading
     *
     * @return $this
     */
    private function setAddOrderInfoFlag()
    {
        $this->addOrderInfoFlag = true;
        return $this;
    }

    /**
     * Add order info to collection
     *
     * @return $this
     */
    private function addOrderInfo()
    {
        if ($this->isNeedToAddOrderInfo()) {
            $orderInfo = $this->getOrderInfo();
            $this->addOrderInfoToItems($orderInfo);
        }
        return $this;
    }

    /**
     * Check if need to add order info to collection items
     *
     * @return bool
     */
    private function isNeedToAddOrderInfo()
    {
        $profileIds = $this->getColumnValues($this->getResource()->getIdFieldName());
        return ($this->addOrderInfoFlag && !empty($profileIds));
    }

    /**
     * Retrieve order info for collection items
     *
     * @return array
     */
    private function getOrderInfo()
    {
        $lastOrdersIds = $this->getColumnValues('last_order_id');
        $orderResourceConnection = $this->orderCollection->getConnection();
        $select = $orderResourceConnection->select();
        $select->from(
            ['sales_order_table' => $this->orderCollection->getTable('sales_order')],
            [
                'last_order_grand_total' => 'sales_order_table.base_grand_total',
                'order_increment_id' => 'sales_order_table.increment_id',
                'entity_id',
            ]
        )->where(
            'sales_order_table.entity_id IN(?)',
            $lastOrdersIds
        );
        $orderInfo = $orderResourceConnection->fetchAll($select);
        return $orderInfo;
    }

    /**
     * Add order data to collection items
     *
     * @param array $orderInfo
     * @return $this
     */
    private function addOrderInfoToItems(array $orderInfo)
    {
        foreach ($this->getItems() as $gridItem) {
            $orderItemInfo = $this->getOrderDataForItem($orderInfo, $gridItem->getDataByKey('last_order_id'));
            $gridItem->addData($orderItemInfo);
        }

        return $this;
    }

    /**
     * Get order info for for the specific collection item
     *
     * @param array $orderInfo
     * @param mixed $entityId
     * @return array
     */
    private function getOrderDataForItem($orderInfo, $entityId)
    {
        $defaultOrderInfo = ['last_order_grand_total' => null];
        $orderItemInfo = [];
        foreach ($orderInfo as $orderItem) {
            if ($orderItem['entity_id'] == $entityId) {
                $orderItemInfo = $orderItem;
                break;
            }
        }
        $mergedOrderInfo = array_merge($defaultOrderInfo, $orderItemInfo);
        return $mergedOrderInfo;
    }

    /**
     * Get customer group field expression
     *
     * @return \Zend_Db_Expr
     */
    private function getGroupExpression()
    {
        return $this->getResource()
            ->getConnection()
            ->getIfNullSql(
                'customer_entity_table.group_id',
                new \Zend_Db_Expr('0')
            );
    }
}
