<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Plan;

use Aheadworks\Sarp2\Model\Plan;
use Aheadworks\Sarp2\Model\ResourceModel\Plan as PlanResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Aheadworks\Sarp2\Model\ResourceModel\Plan
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'plan_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Plan::class, PlanResource::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachDefinition();
        $this->attachTitles();
        return parent::_afterLoad();
    }

    /**
     * Attach definition data to collection's items
     *
     * @return void
     */
    private function attachDefinition()
    {
        $definitionIds = $this->getColumnValues('definition_id');
        if (count($definitionIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['definition_table' => $this->getTable('aw_sarp2_plan_definition')])
                ->where('definition_table.definition_id IN (?)', $definitionIds);
            $definitionRows = $connection->fetchAll($select);

            /** @var Plan $item */
            foreach ($this as $item) {
                $definitionId = $item->getDefinitionId();
                foreach ($definitionRows as $row) {
                    if ($row['definition_id'] == $definitionId) {
                        $item->setDefinition($row);
                    }
                }
            }
        }
    }

    /**
     * Attach titles data to collection's items
     *
     * @return void
     */
    private function attachTitles()
    {
        $ids = $this->getAllIds();
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['title_table' => $this->getTable('aw_sarp2_plan_title')])
                ->where('title_table.plan_id IN (?)', $ids);

            /** @var Plan $item */
            foreach ($this as $item) {
                $titles = [];
                $id = $item->getPlanId();
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data['plan_id'] == $id) {
                        $titles[] = $data;
                    }
                }
                $item->setTitles($titles);
            }
        }
    }
}
