<?php
namespace Aheadworks\Sarp2\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;

class AddActionRestrictConstraint implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup
    ) {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Add action default constraint manually because of Magento issue
     *
     * Issue: https://github.com/magento/magento2/issues/27072
     */
    public function apply()
    {
        $connection = $this->schemaSetup->getConnection();
        $connection->addForeignKey(
            $this->schemaSetup->getFkName(
                'aw_sarp2_profile',
                'payment_token_id',
                'aw_sarp2_payment_token',
                'token_id'
            ),
            $this->schemaSetup->getTable('aw_sarp2_profile'),
            'payment_token_id',
            $this->schemaSetup->getTable('aw_sarp2_payment_token'),
            'token_id',
            Table::ACTION_RESTRICT
        );
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
