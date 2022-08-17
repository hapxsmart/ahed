<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Setup\Uninstall\Sequence\DeleteByEntityType;

class RemoveProfileSequence implements DataPatchInterface, PatchRevertableInterface, PatchVersionInterface
{
    /**
     * @var DeleteByEntityType
     */
    private $deleteSequenceByEntityType;

    /**
     * @param DeleteByEntityType $deleteSequenceByEntityType
     */
    public function __construct(
        DeleteByEntityType $deleteSequenceByEntityType
    ) {
        $this->deleteSequenceByEntityType = $deleteSequenceByEntityType;
    }

    /**
     * Skip installing patch
     */
    public function apply()
    {
        return true;
    }

    /**
     * Delete sequence
     *
     * @throws \Exception
     */
    public function revert()
    {
        $this->deleteSequenceByEntityType->execute(Profile::ENTITY);
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
        return '2.0.0';
    }
}
