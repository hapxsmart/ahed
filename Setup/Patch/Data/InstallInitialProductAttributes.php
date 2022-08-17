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
use Aheadworks\Sarp2\Model\Product\Attribute\Backend\SubscriptionOptions as BackendSubscriptionOptions;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType as SubscriptionTypeSource;
use Aheadworks\Sarp2\Model\Product\Type\Plugin\Config;

class InstallInitialProductAttributes implements DataPatchInterface, PatchRevertableInterface, PatchVersionInterface
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
     * Install initial product attributes
     *
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $applyTo = implode(
            ',',
            $this->productTypeConfig->filter(Config::SUPPORTED_CUSTOM_ATTR_CODE, true)
        );

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'aw_sarp2_subscription_type',
            [
                'type' => 'int',
                'group' => 'Sarp2: Subscription Configuration',
                'label' => 'Subscription',
                'input' => 'select',
                'sort_order' => 1,
                'source' => SubscriptionTypeSource::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => SubscriptionTypeSource::NO,
                'apply_to' => $applyTo,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true
            ]
        )->addAttribute(
            Product::ENTITY,
            'aw_sarp2_subscription_options',
            [
                'type' => 'decimal',
                'group' => 'Sarp2: Subscription Configuration',
                'label' => 'Subscription Options',
                'input' => 'text',
                'backend' => BackendSubscriptionOptions::class,
                'sort_order' => 2,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'apply_to' => $applyTo,
                'visible_on_front' => false
            ]
        );
    }

    /**
     * Remove product attributes
     */
    public function revert()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(Product::ENTITY, 'aw_sarp2_subscription_type');
        $eavSetup->removeAttribute(Product::ENTITY, 'aw_sarp2_subscription_options');
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
