<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\HashGenerator;

class AddHashToExistingProfiles implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var HashGenerator
     */
    private $hashGenerator;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param HashGenerator $hashGenerator
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        HashGenerator $hashGenerator
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->hashGenerator = $hashGenerator;
    }

    /**
     * Apply data patch
     */
    public function apply()
    {
        $this->addHashToExistingProfiles($this->moduleDataSetup);
    }

    /**
     * Add hash to existing profiles
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addHashToExistingProfiles(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $select = $connection->select()
            ->from($setup->getTable('aw_sarp2_profile'), ProfileInterface::PROFILE_ID)
            ->where(ProfileInterface::HASH . ' IS NULL');
        $profiles = $connection->fetchAll($select);
        foreach ($profiles as &$profile) {
            $profile[ProfileInterface::HASH] = $this->hashGenerator->generate($profile[ProfileInterface::PROFILE_ID]);
        }

        if ($profiles) {
            $connection->insertOnDuplicate($setup->getTable('aw_sarp2_profile'), $profiles);
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
        return '2.16.0';
    }
}
