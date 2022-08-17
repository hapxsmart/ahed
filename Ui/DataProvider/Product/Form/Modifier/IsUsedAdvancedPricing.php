<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier;

use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName as Attribute;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form\Element\Checkbox;
use Magento\Ui\Component\Form\Field;

/**
 * Class IsUsedAdvancedPricing
 *
 * @package Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier
 */
class IsUsedAdvancedPricing extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param Config $config
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        Config $config
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $id = $this->locator->getProduct()->getId();
        $useConfigValue = Boolean::VALUE_USE_CONFIG;

        $isConfigUsed =
            ($data[$id][self::DATA_SOURCE_DEFAULT][Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING] ?? $useConfigValue)
                == $useConfigValue;

        if ($isConfigUsed || empty($id)) {
            $data[$id][self::DATA_SOURCE_DEFAULT][Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING] =
                (string)(int)$this->config->isUsedAdvancedPricing();
            $data[$id][self::DATA_SOURCE_DEFAULT]['use_config_' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING] = '1';
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $groupCode = $this->getGroupCodeByField($meta, 'container_' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING);

        if (!$groupCode) {
            return $meta;
        }

        $containerPath = $this->arrayManager->findPath(
            'container_' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING,
            $meta,
            null,
            'children'
        );
        $fieldPath = $this->arrayManager->findPath(
            Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING,
            $meta, null,
            'children'
        );
        $fieldConfig = $this->arrayManager->get($fieldPath, $meta);

        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'container',
                            'componentType' => 'container',
                            'component' => 'Magento_Ui/js/form/components/group',
                            'label' => false,
                            'required' => false,
                            'breakLine' => false,
                            'sortOrder' => $fieldConfig['arguments']['data']['config']['sortOrder'],
                            'dataScope' => '',
                        ],
                    ],
                ],
            ]
        );
        $meta = $this->arrayManager->merge(
            $containerPath,
            $meta,
            [
                'children' => [
                    Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataScope' => Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING,
                                    'additionalClasses' => 'admin__field-x-small',
                                    'component' => 'Magento_Ui/js/form/element/single-checkbox-use-config',
                                    'componentType' => Field::NAME,
                                    'prefer' => 'toggle',
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'use_config_' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType' => 'number',
                                    'formElement' => Checkbox::NAME,
                                    'componentType' => Field::NAME,
                                    'description' => __('Use Config Settings'),
                                    'dataScope' => 'use_config_' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING,
                                    'valueMap' => [
                                        'false' => '0',
                                        'true' => '1',
                                    ],
                                    'exports' => [
                                        'checked' => '${$.parentName}.' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING
                                            . ':isUseConfig',
                                        '__disableTmpl' => ['checked' => false],
                                    ],
                                    'imports' => [
                                        'disabled' => '${$.parentName}.' . Attribute::AW_SARP2_IS_USED_ADVANCED_PRICING
                                            . ':isUseDefault',
                                        '__disableTmpl' => ['disabled' => false],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );

        return $meta;
    }
}
