<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateMembershipFields implements DataPatchInterface, PatchVersionInterface
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
        $this->updateMembershipFields($this->moduleDataSetup);
    }

    /**
     * Update membership fields
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function updateMembershipFields(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $table = $setup->getTable('aw_sarp2_core_schedule');

        // for expired subscriptions
        $connection->query(
            'UPDATE ' . $table .
            ' SET
                    regular_total_count = regular_total_count - 1,
                    regular_count = regular_count - 1,
                    membership_total_count = 1,
                    membership_count = 1' .
            ' WHERE is_membership_model = 1
                    AND regular_total_count > 0
                    AND regular_total_count = regular_count'
        );

        // for Not expired subscriptions
        $connection->query(
            'UPDATE ' . $table .
            ' SET
                    regular_total_count = regular_total_count - 1,
                    membership_total_count = 1' .
            ' WHERE is_membership_model = 1
                    AND regular_total_count > 0
                    AND regular_total_count <> regular_count'
        );

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
        return '2.7.0';
    }
}
