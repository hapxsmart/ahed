<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Plan\Handler;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Api\Data\PlanInterface;
use Aheadworks\Sarp2\Model\Plan\Definition;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Class UpdateProfileDefinitionFields
 *
 * @package Aheadworks\Sarp2\Model\ResourceModel\Plan\Handler
 */
class UpdateProfileDefinitionFields implements HandlerInterface
{
    /**
     * @var string[]
     */
    private $copiedFields = [
        PlanDefinitionInterface::IS_EXTEND_ENABLE,
        PlanDefinitionInterface::OFFER_EXTEND_EMAIL_OFFSET,
        PlanDefinitionInterface::OFFER_EXTEND_EMAIL_TEMPLATE,

        PlanDefinitionInterface::IS_ALLOW_SUBSCRIPTION_CANCELLATION
    ];

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param DataObjectProcessor $dataObjectProcessor
     * @param string[] $copiedFields
     */
    public function __construct(
        DataObjectProcessor $dataObjectProcessor,
        array $copiedFields = []
    ) {
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->copiedFields = array_merge($this->copiedFields, $copiedFields);
    }

    /**
     * Process plan saving/deletion
     *
     * @param AbstractDb $resourceModel
     * @param PlanInterface $plan
     * @return void
     */
    public function process(AbstractDb $resourceModel, PlanInterface $plan)
    {
        $connection = $resourceModel->getConnection();
        $data = $this->getUpdateData($plan);

        if (!empty($data)) {
            $updateSql = $this->createUpdateQuery($resourceModel, $data, $plan->getDefinitionId());
            $connection->query($updateSql, array_values($data));
        }
    }

    /**
     * Retrieve update data
     *
     * @param PlanInterface $plan
     * @return array
     */
    private function getUpdateData(PlanInterface $plan)
    {
        $origDefinition = $plan->getOrigData('definition');
        if ($origDefinition && $origDefinition instanceof PlanDefinitionInterface) {
            $origDefinition = $this->dataObjectProcessor->buildOutputDataArray(
                $origDefinition,
                PlanDefinitionInterface::class
            );
        } else {
            $origDefinition = [];
        }

        $newDefinition = $this->dataObjectProcessor->buildOutputDataArray(
            $plan->getDefinition(),
            PlanDefinitionInterface::class
        );

        $data = [];
        foreach ($this->copiedFields as $field) {
            if (!array_key_exists($field, $newDefinition)) {
                continue;
            }
            if (array_key_exists($field, $origDefinition)
                && $newDefinition[$field] == $origDefinition[$field]
            ) {
                continue;
            }
            $data[$field] = $newDefinition[$field];
        }

        return $data;
    }

    /**
     * Create update sql query
     *
     * @param AbstractDb $resourceModel
     * @param array $setFields
     * @param int $planDefinitionId
     * @return string
     */
    private function createUpdateQuery($resourceModel, array $setFields, $planDefinitionId)
    {
        $profileTable = $resourceModel->getTable('aw_sarp2_profile');
        $profileDefinitionTable = $resourceModel->getTable('aw_sarp2_profile_definition');
        $connection = $resourceModel->getConnection();

        $set = [];
        foreach ($setFields as $column => $value) {
            $set[] = $connection->quoteIdentifier($column, true) . ' = ?';
        }

        $joinExpr = sprintf(
            ' INNER JOIN %s ON %s.definition_id = %s.profile_definition_id ',
            $profileTable,
            $profileDefinitionTable,
            $profileTable
        );
        $whereExpr = sprintf(' WHERE %s.plan_definition_id = %s', $profileTable, $planDefinitionId);

        $sql = "UPDATE "
            . $profileDefinitionTable
            . $joinExpr
            . ' SET ' . implode(', ', $set)
            . $whereExpr;

        return $sql;
    }
}
