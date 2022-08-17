<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option;

use Aheadworks\Sarp2\Model\Product\Subscription\Option;
use Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription\Option as OptionResource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'option_id';

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Option::class, OptionResource::class);
    }

    /**
     * Add product filter to collection
     *
     * @param int $productId
     * @return $this
     */
    public function addProductFilter($productId)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $this->getSelect()
            ->joinLeft(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                'product_entity.' . $linkField . ' = main_table.product_id',
                []
            )
            ->where('product_entity.entity_id = ?', $productId);
        return $this;
    }

    /**
     * Add store filter to collection
     *
     * @param int|array $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $connection = $this->getConnection();
        $select = $this->getSelect();

        if (!isset($select->getPart('from')['plan_title_store']['tableName'])) {
            $websiteIds = [0];
            $storeIds = is_array($storeId) ? $storeId : [$storeId];
            foreach ($storeIds as $storeId) {
                $store = $this->storeManager->getStore($storeId);
                $websiteIds[] = $store->getWebsiteId();
            }

            $select
                ->where('main_table.website_id IN (?)', $websiteIds)
                ->joinLeft(
                    ['plan_title_store' => $this->getTable('aw_sarp2_plan_title')],
                    $connection->quoteInto(
                        'plan_title_store.plan_id = main_table.plan_id AND plan_title_store.store_id IN (?)',
                        $storeIds
                    ),
                    []
                )
                ->joinLeft(
                    ['plan_title_default' => $this->getTable('aw_sarp2_plan_title')],
                    $connection->quoteInto(
                        'plan_title_default.plan_id = main_table.plan_id AND plan_title_default.store_id = ?',
                        0
                    ),
                    []
                )
                ->joinLeft(
                    ['plan_title_backend' => $this->getTable('aw_sarp2_plan')],
                    'plan_title_backend.plan_id = main_table.plan_id',
                    ['backend_title' => 'plan_title_backend.name']
                );

            $columns = $select->getPart(Select::COLUMNS);
            $columns[] = [
                'main_table',
                new \Zend_Db_Expr(
                    "IFNULL(plan_title_store.title, plan_title_default.title)"
                ),
                'frontend_title'
            ];
            $select->setPart(Select::COLUMNS, $columns);
        }

        return $this;
    }
}
