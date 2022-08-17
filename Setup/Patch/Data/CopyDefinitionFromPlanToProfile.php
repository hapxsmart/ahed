<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class CopyDefinitionFromPlanToProfile implements DataPatchInterface, PatchVersionInterface
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
        $this->copyDefinitionFromPlanToProfile($this->moduleDataSetup);
    }

    /**
     * Copy definition from plan to profile
     *
     * @param ModuleDataSetupInterface $installer
     * @return $this
     */
    public function copyDefinitionFromPlanToProfile(ModuleDataSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_profile'),
            ['plan_definition_id']
        )->group('plan_definition_id');
        $planDefinitionIds = $connection->fetchAssoc($select);

        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_plan_definition')
        )->where('definition_id IN (?)', $planDefinitionIds);
        $planDefinitions = $connection->fetchAssoc($select);

        $select = $connection->select()->from(
            $installer->getTable('aw_sarp2_profile'),
            ['profile_id', 'plan_definition_id']
        );
        $profileDefinitions = $connection->fetchAssoc($select);

        foreach ($profileDefinitions as $profile) {
            $planDefinitionId = $profile['plan_definition_id'];
            $profileId = $profile['profile_id'];
            if (!isset($planDefinitions[$planDefinitionId])) {
                continue;
            }

            $planDefinitionData = $planDefinitions[$planDefinitionId];
            unset($planDefinitionData['definition_id']);

            $connection->insert(
                $installer->getTable('aw_sarp2_profile_definition'),
                $planDefinitionData
            );
            $profileDefinitionId = $connection->lastInsertId('aw_sarp2_profile_definition');

            $connection->update(
                $installer->getTable('aw_sarp2_profile'),
                ['profile_definition_id' => $profileDefinitionId],
                ['profile_id = ?' => $profileId]
            );
        }
        return $this;
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

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.2.0';
    }
}
