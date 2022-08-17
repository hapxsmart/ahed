<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;

class UpdateSubscriptionStatistics implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Install data patch
     */
    public function apply()
    {
        $this->updateIsInitialColumnInProfileOrderTable();
    }

    /**
     * Update 'is_initial' column in 'aw_sarp2_profile_order' table
     */
    private function updateIsInitialColumnInProfileOrderTable()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $innerSelect = $connection->select()
            ->from(
                ['inner_so_table' => $this->moduleDataSetup->getTable('sales_order')],
                ['first_order_created_at' => 'MIN(inner_so_table.created_at)']
            )->joinInner(
                ['inner_po_table' => $this->moduleDataSetup->getTable('aw_sarp2_profile_order')],
                'inner_po_table.order_id = inner_so_table.entity_id',
                []
            )->group('inner_po_table.profile_id');

        $mainSelect = $connection->select()
            ->from(['so_table' => $this->moduleDataSetup->getTable('sales_order')], ['entity_id'])
            ->joinInner(
                ['po_table' => $this->moduleDataSetup->getTable('aw_sarp2_profile_order')],
                'po_table.order_id = so_table.entity_id',
                []
            )->joinInner(
                ['sopo_table' => $innerSelect],
                'sopo_table.first_order_created_at = so_table.created_at',
                []
            );

        $orderIds = $connection->fetchCol($mainSelect);
        $orderIdGroups = array_chunk($orderIds, 5000);
        foreach ($orderIdGroups as $orderIdGroup) {
            $connection->update(
                $this->moduleDataSetup->getTable('aw_sarp2_profile_order'),
                [
                    ProfileOrderInterface::IS_INITIAL => 1
                ],
                [ProfileOrderInterface::ORDER_ID . ' IN (?)' => $orderIdGroup]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
