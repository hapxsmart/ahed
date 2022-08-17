<?php
namespace Aheadworks\Sarp2\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName;
use Aheadworks\Sarp2\Model\Product\Type\Plugin\Config;

class CreateAdvancedPricingProductAttribute implements
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
     * Create advanced pricing attribute
     *
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->updateAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_SUBSCRIPTION_TYPE,
            'sort_order',
            10,
            10
        );
        $eavSetup->updateAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_SUBSCRIPTION_OPTIONS,
            'sort_order',
            30,
            30
        );

        $applyTo = implode(
            ',',
            $this->productTypeConfig->filter(Config::SUPPORTED_CUSTOM_ATTR_CODE, true)
        );
        $eavSetup->addAttribute(
            Product::ENTITY,
            AttributeName::AW_SARP2_IS_USED_ADVANCED_PRICING,
            [
                'type' => 'int',
                'group' => 'Sarp2: Subscription Configuration',
                'label' => 'Use Product Advanced Pricing',
                'input' => 'select',
                'sort_order' => 20,
                'backend' => \Aheadworks\Sarp2\Model\Product\Attribute\Backend\BooleanWithConfig::class,
                'source' => \Magento\Catalog\Model\Product\Attribute\Source\Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => \Magento\Catalog\Model\Product\Attribute\Source\Boolean::VALUE_USE_CONFIG,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
            ]
        );
    }

    /**
     * Remove advanced pricing attributes
     */
    public function revert()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'aw_sarp2_use_advanced_pricing');
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
            ChangeFrontInputRendererForSubsOptAttr::class
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.12.0';
    }
}
