<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName;
use Aheadworks\Sarp2\Model\Product\Type\Plugin\Config;

class UpdateProductEavAttributes implements
    DataPatchInterface,
    PatchRevertableInterface,
    PatchVersionInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ConfigInterface
     */
    private $productTypeConfig;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ConfigInterface $productTypeConfig
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ConfigInterface $productTypeConfig
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productTypeConfig = $productTypeConfig;
    }

    /**
     * Update product eav attributes
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $applyTo = implode(
            ',',
            $this->productTypeConfig->filter(Config::SUPPORTED_CUSTOM_ATTR_CODE, true)
        );

        $eavSetup->updateAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_SUBSCRIPTION_TYPE,
            'apply_to',
            $applyTo
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_SUBSCRIPTION_OPTIONS,
            'apply_to',
            $applyTo
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_IS_USED_ADVANCED_PRICING,
            'apply_to',
            $applyTo
        );
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        return true;
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
        return [
            CreateAdvancedPricingProductAttribute::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.14.0';
    }
}
