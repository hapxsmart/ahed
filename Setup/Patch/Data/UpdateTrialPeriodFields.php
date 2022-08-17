<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpdateTrialPeriodFields implements DataPatchInterface, PatchVersionInterface
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
        $this->updateTrialPeriodFields($this->moduleDataSetup);
    }

    /**
     * Update separate trial period fields
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function updateTrialPeriodFields(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $tables = ['aw_sarp2_plan_definition', 'aw_sarp2_profile_definition'];
        foreach ($tables as $table) {
            $connection->query(
                'UPDATE ' . $setup->getTable($table) .
                ' SET
                    trial_billing_period = billing_period,
                    trial_billing_frequency = billing_frequency,
                    upcoming_trial_billing_email_offset = upcoming_billing_email_offset' .
                ' WHERE is_trial_period_enabled = 1'
            );
        }

        $connection->query(
            'UPDATE ' . $setup->getTable('aw_sarp2_core_schedule') .
            ' SET
                    trial_period = period,
                    trial_frequency = frequency'
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
