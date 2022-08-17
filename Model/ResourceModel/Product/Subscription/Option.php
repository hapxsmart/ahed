<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription;

use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Definition\Loader as DefinitionLoader;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class Option
 * @package Aheadworks\Sarp2\Model\ResourceModel\Product\Subscription
 */
class Option extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var PlanInterfaceFactory
     */
    private $planFactory;

    /**
     * @var DefinitionLoader
     */
    private $definitionLoader;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param PlanInterfaceFactory $planFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DefinitionLoader $definitionLoader
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        PlanInterfaceFactory $planFactory,
        DataObjectHelper $dataObjectHelper,
        DefinitionLoader $definitionLoader,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->planFactory = $planFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->definitionLoader = $definitionLoader;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_subscription_option', 'option_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->_resources->getConnectionByName(
            $this->metadataPool->getMetadata(PlanInterface::class)->getEntityConnectionName()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadPlan($object);
        return parent::_afterLoad($object);
    }

    /**
     * Load options data
     *
     * @param int $productId
     * @param int $websiteId
     * @return array
     * @throws LocalizedException
     */
    public function loadOptionsData($productId, $websiteId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('product_id = ?', $productId);
        if ($websiteId != 0) {
            $select->where('website_id IN (?)', [0, $websiteId]);
        }

        return $connection->fetchAll($select);
    }

    /**
     * Save options data
     *
     * @param DataObject $optionsObject
     * @return $this
     * @throws LocalizedException
     */
    public function saveOptionsData(DataObject $optionsObject)
    {
        $connection = $this->getConnection();

        $data = $this->_prepareDataForTable($optionsObject, $this->getMainTable());

        if (!empty($data[$this->getIdFieldName()])) {
            $where = $connection->quoteInto($this->getIdFieldName() . ' = ?', $data[$this->getIdFieldName()]);
            unset($data[$this->getIdFieldName()]);
            $connection->update($this->getMainTable(), $data, $where);
        } else {
            $connection->insert($this->getMainTable(), $data);
        }
        return $this;
    }

    /**
     * Delete options data
     *
     * @param int $productId
     * @param int $websiteId
     * @param int|null $optionId
     * @return int
     * @throws LocalizedException
     */
    public function deleteOptionsData($productId, $websiteId, $optionId = null)
    {
        $connection = $this->getConnection();

        $conditions = [
            $connection->quoteInto('product_id = ?', $productId),
            $connection->quoteInto('website_id = ?', $websiteId)
        ];
        if ($optionId) {
            $conditions[] = $connection->quoteInto($this->getIdFieldName() . ' = ?', $optionId);
        }
        return $connection->delete($this->getMainTable(), implode(' AND ', $conditions));
    }

    /**
     * Load plan
     *
     * @param SubscriptionOptionInterface|AbstractModel $option
     * @return void
     */
    private function loadPlan(SubscriptionOptionInterface $option)
    {
        $connection = $this->getConnection();

        $planId = $option->getPlanId();
        if ($planId) {
            $select = $connection->select()
                ->from($this->getTable('aw_sarp2_plan'))
                ->where('plan_id = ?', $planId);
            $data = $connection->fetchRow($select);

            /** @var PlanInterface $plan */
            $plan = $this->planFactory->create();
            $this->dataObjectHelper->populateWithArray($plan, $data, PlanInterface::class);

            $definitionId = $plan->getDefinitionId();
            if ($definitionId) {
                $plan->setDefinition(
                    $this->definitionLoader->loadDefinition($definitionId, true)
                );
            }

            $option->setPlan($plan);
        }
    }

    /**
     * Get product entity Id
     *
     * @param int $optionId
     * @return int
     */
    public function getProductEntityId($optionId)
    {
        $connection = $this->getConnection();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $mainTable = $this->getMainTable();

        $select = $connection->select()
            ->from($mainTable, [])
            ->joinLeft(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                'product_entity.' . $linkField . ' = ' . $mainTable . '.product_id',
                ['product_entity.entity_id']
            )
            ->where($mainTable . '.option_id = ?', $optionId);

        return (int)$connection->fetchOne($select);
    }
}
