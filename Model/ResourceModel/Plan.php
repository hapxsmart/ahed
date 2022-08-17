<?php
namespace Aheadworks\Sarp2\Model\ResourceModel;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterface;
use Aheadworks\Sarp2\Api\Data\PlanTitleInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Definition\Loader as DefinitionLoader;
use Aheadworks\Sarp2\Model\ResourceModel\Plan\Handler\HandlerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class Plan
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel
 */
class Plan extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var Factory
     */
    private $dataObjectFactory;

    /**
     * @var DefinitionLoader
     */
    private $definitionLoader;

    /**
     * @var PlanTitleInterfaceFactory
     */
    private $titleFactory;

    /**
     * @var HandlerInterface[]
     */
    private $saveHandlers = [];

    /**
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param DataObjectProcessor $dataObjectProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param Factory $dataObjectFactory
     * @param DefinitionLoader $definitionLoader
     * @param PlanTitleInterfaceFactory $titleFactory
     * @param array $saveHandlers
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        DataObjectProcessor $dataObjectProcessor,
        DataObjectHelper $dataObjectHelper,
        Factory $dataObjectFactory,
        DefinitionLoader $definitionLoader,
        PlanTitleInterfaceFactory $titleFactory,
        $saveHandlers = [],
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->definitionLoader = $definitionLoader;
        $this->titleFactory = $titleFactory;
        $this->saveHandlers = $saveHandlers;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sarp2_plan', 'plan_id');
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
    protected function _beforeSave(AbstractModel $object)
    {
        $this->saveDefinition($object);
        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveTitles($object);

        /** @var PlanInterface|AbstractModel $object */
        foreach ($this->saveHandlers as $handler) {
            $handler->process($this, $object);
        }

        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadDefinition($object);
        $this->loadTitles($object);
        return parent::_afterLoad($object);
    }

    /**
     * Save plan frontend titles
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function saveTitles(PlanInterface $plan)
    {
        $planId = $plan->getPlanId();

        $table = $this->getTable('aw_sarp2_plan_title');
        $connection = $this->getConnection();

        $connection->delete($table, ['plan_id = ?' => $planId]);
        $toInsert = [];
        foreach ($plan->getTitles() as $title) {
            $title->setPlanId($planId);
            $titleData = $this->dataObjectProcessor->buildOutputDataArray(
                $title,
                PlanTitleInterface::class
            );
            $toInsert[] = $this->_prepareDataForTable(
                $this->dataObjectFactory->create($titleData),
                $table
            );
        }
        if ($toInsert) {
            $connection->insertMultiple($table, $toInsert);
        }
    }

    /**
     * Load definition
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function loadDefinition(PlanInterface $plan)
    {
        $definitionId = $plan->getDefinitionId();
        if ($definitionId) {
            $plan->setDefinition(
                $this->definitionLoader->loadDefinition($definitionId, true)
            );
        }
    }

    /**
     * Save plan definition
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function saveDefinition(PlanInterface $plan)
    {
        $table = $this->getTable('aw_sarp2_plan_definition');
        $connection = $this->getConnection();

        $definition = $plan->getDefinition();
        $definitionId = $definition->getDefinitionId();
        $definitionData = $this->dataObjectProcessor->buildOutputDataArray(
            $definition,
            PlanDefinitionInterface::class
        );
        $data = $this->_prepareDataForTable(
            $this->dataObjectFactory->create($definitionData),
            $table
        );
        if ($definitionId) {
            $connection->update($table, $data, ['definition_id = ?' => $definitionId]);
        } else {
            $connection->insert($table, $data);
            $plan->setDefinitionId($connection->lastInsertId($table));
        }
    }

    /**
     * Load plan titles
     *
     * @param PlanInterface|AbstractModel $plan
     * @return void
     */
    private function loadTitles(PlanInterface $plan)
    {
        $connection = $this->getConnection();

        $planId = $plan->getPlanId();
        if ($planId) {
            $select = $connection->select()
                ->from($this->getTable('aw_sarp2_plan_title'))
                ->where('plan_id = ?', $planId);
            $rows = $connection->fetchAll($select);

            $titles = [];
            foreach ($rows as $row) {
                /** @var PlanTitleInterface $title */
                $title = $this->titleFactory->create();
                $this->dataObjectHelper->populateWithArray($title, $row, PlanTitleInterface::class);
                $titles[] = $title;
            }
            $plan->setTitles($titles);
        }
    }
}
