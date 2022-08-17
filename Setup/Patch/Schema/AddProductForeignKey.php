<?php
namespace Aheadworks\Sarp2\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;

class AddProductForeignKey implements SchemaPatchInterface, PatchVersionInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param SchemaSetupInterface $schemaSetup
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        MetadataPool $metadataPool
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Apply schema patch
     *
     * @throws \Exception
     */
    public function apply()
    {
        $connection = $this->schemaSetup->getConnection();
        $connection->addForeignKey(
            $this->schemaSetup->getFkName(
                'aw_sarp2_subscription_option',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            $this->schemaSetup->getTable('aw_sarp2_subscription_option'),
            'product_id',
            $this->schemaSetup->getTable('catalog_product_entity'),
            $this->metadataPool->getMetadata(ProductInterface::class)
                ->getLinkField(),
            Table::ACTION_CASCADE
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
