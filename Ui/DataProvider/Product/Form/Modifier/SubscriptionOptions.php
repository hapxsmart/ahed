<?php
namespace Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier;

use Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface;
use Aheadworks\Sarp2\Block\Adminhtml\Product\SubscriptionOptions\Notice as NoticeBlock;
use Aheadworks\Sarp2\Model\Config\Source\NoYes;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\ScopedPlan;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\WebsiteId;
use Aheadworks\Sarp2\Model\Product\Checker\IsChildOfConfigurable as IsChildOfConfigurableChecker;
use Aheadworks\Sarp2\Ui\DataProvider\Product\Form\Modifier\SubscriptionOptions\PlanOptionsProvider;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Currency;
use Magento\Framework\Locale\CurrencyInterface;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Element\DataType\Boolean;
use Magento\Ui\Component\Form\Element\DataType\Price;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Hidden;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;

class SubscriptionOptions extends AbstractModifier
{
    /**
     * Subscription options attribute code
     */
    const SUBSCRIPTION_OPTIONS_ATTR_CODE = 'aw_sarp2_subscription_options';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var ScopedPlan
     */
    private $planSource;

    /**
     * @var WebsiteId
     */
    private $websiteSource;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var NoYes
     */
    private $yesNoSource;

    /**
     * @var IsChildOfConfigurableChecker
     */
    private $isChildOfConfigurableChecker;

    /**
     * @var PlanOptionsProvider
     */
    private $planOptionsProvider;

    /**
     * @param ArrayManager $arrayManager
     * @param BlockFactory $blockFactory
     * @param ScopedPlan $planSource
     * @param WebsiteId $websiteSource
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param CurrencyInterface $localeCurrency
     * @param PriceCurrency $priceCurrency
     * @param NoYes $yesNoSource
     * @param IsChildOfConfigurableChecker $isChildOfConfigurableChecker
     * @param PlanOptionsProvider $planOptionsProvider
     */
    public function __construct(
        ArrayManager $arrayManager,
        BlockFactory $blockFactory,
        ScopedPlan $planSource,
        WebsiteId $websiteSource,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        CurrencyInterface $localeCurrency,
        PriceCurrency $priceCurrency,
        NoYes $yesNoSource,
        IsChildOfConfigurableChecker $isChildOfConfigurableChecker,
        PlanOptionsProvider $planOptionsProvider
    ) {
        $this->arrayManager = $arrayManager;
        $this->blockFactory = $blockFactory;
        $this->planSource = $planSource;
        $this->websiteSource = $websiteSource;
        $this->locator = $locator;
        $this->storeManager = $storeManager;
        $this->localeCurrency = $localeCurrency;
        $this->priceCurrency = $priceCurrency;
        $this->yesNoSource = $yesNoSource;
        $this->isChildOfConfigurableChecker = $isChildOfConfigurableChecker;
        $this->planOptionsProvider = $planOptionsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $subscriptionOptionsContainerPath = $this->arrayManager->findPath(
            self::CONTAINER_PREFIX . self::SUBSCRIPTION_OPTIONS_ATTR_CODE,
            $meta,
            null,
            'children'
        );

        $hasPlanOptions = count($this->planSource->toOptionArray()) > 0;
        if ($hasPlanOptions) {
            $subscriptionOptionsPath = $this->arrayManager->findPath(
                self::SUBSCRIPTION_OPTIONS_ATTR_CODE,
                $meta,
                null,
                'children'
            );
            if ($subscriptionOptionsPath) {
                $product = $this->locator->getProduct();

                $meta = $this->arrayManager->merge(
                    $subscriptionOptionsPath,
                    $meta,
                    $this->getSubscriptionOptionsStructure()
                );

                if (($product->getTypeId() == Configurable::TYPE_CODE
                        || $this->isChildOfConfigurableChecker->check($product)
                    )
                    && $subscriptionOptionsContainerPath
                ) {
                    $meta = $this->arrayManager->merge(
                        $subscriptionOptionsContainerPath . '/children',
                        $meta,
                        $this->getChildConfigNoticeStructure()
                    );
                }
            }
        } elseif ($subscriptionOptionsContainerPath) {
            $meta = $this->arrayManager->merge(
                $subscriptionOptionsContainerPath,
                $meta,
                $this->getNoOptionsNoticeStructure()
            );
            $meta = $this->arrayManager->remove(
                $subscriptionOptionsContainerPath . '/children',
                $meta
            );
        }
        return $meta;
    }

    /**
     * Get subscription options component structure
     *
     * @return array
     */
    private function getSubscriptionOptionsStructure()
    {
        $product = $this->locator->getProduct();

        /** @var Store $store */
        $store = $this->locator->getStore();
        $currencySymbol = $store->getBaseCurrency()
            ->getCurrencySymbol();

        $priceWithAutoValueStructure = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'formElement' => Input::NAME,
                        'dataType' => Price::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Aheadworks_Sarp2/js/ui/form/element/product/'
                            . 'price-input-with-auto',
                        'enableLabel' => true,
                        'validation' => [
                            'required-entry' => true
                        ],
                        'service' => [
                            'template' => 'Aheadworks_Sarp2/ui/form/element/product/helper/'
                                . 'use-auto-service',
                        ],
                        'isForceDisabled' => $product->getTypeId() == BundleType::TYPE_CODE
                    ]
                ]
            ]
        ];
        if ($product->getTypeId() != Configurable::TYPE_CODE) {
            $priceWithAutoValueStructure = $this->arrayManager->merge(
                'arguments/data/config',
                $priceWithAutoValueStructure,
                [
                    'addbefore' => $currencySymbol,
                    'isAutoValueFloat' => true
                ]
            );
        } else {
            $priceWithAutoValueStructure = $this->arrayManager->merge(
                'arguments/data/config',
                $priceWithAutoValueStructure,
                ['isAutoValueFloat' => false]
            );
        }

        $installmentsModeHint = 'Split and add the price of custom option equally to each regular payment.';
        $installmentsModeHint .= ' It will be ignored if the subscription is infinite.';

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'disabled' => false,
                        'required' => false,
                        'additionalClasses' => 'aw-sarp2_subscription-options'
                    ]
                ]
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => ''
                            ]
                        ]
                    ],
                    'children' => [
                        'option_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => Field::NAME,
                                        'formElement' => Hidden::NAME,
                                        'dataType' => Text::NAME,
                                        'visible' => false,
                                        'dataScope' => 'option_id',
                                        'sortOrder' => 200
                                    ]
                                ]
                            ]
                        ],
                        'website_id' => array_merge_recursive(
                            $this->getWebsiteIdStructure(),
                            [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'dataScope' => 'website_id',
                                            'sortOrder' => 10
                                        ]
                                    ]
                                ]
                            ]
                        ),
                        'plan_id' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'component' => 'Aheadworks_Sarp2/js/ui/form/element/product/plan-select',
                                        'dataScope' => 'plan_id',
                                        'label' => __('Plan'),
                                        'options' => $this->planOptionsProvider->getOptions($product),
                                        'sortOrder' => 20
                                    ]
                                ]
                            ]
                        ],
                        'initial_fee' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Input::NAME,
                                        'dataType' => Price::NAME,
                                        'componentType' => Field::NAME,
                                        'component' => 'Aheadworks_Sarp2/js/ui/form/element/product/price-input',
                                        'label' => __('Initial Fee'),
                                        'enableLabel' => true,
                                        'dataScope' => 'initial_fee',
                                        'addbefore' => $currencySymbol,
                                        'sortOrder' => 30,
                                        'validation' => [
                                            'required-entry' => true,
                                            'validate-greater-than-zero' => true
                                        ],
                                        'required' => false
                                    ]
                                ]
                            ]
                        ],
                        'trial_price' => array_merge_recursive(
                            $priceWithAutoValueStructure,
                            [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label' => __('Trial'),
                                            'dataScope' => 'trial_price',
                                            'sortOrder' => 40,
                                            'required' => false
                                        ]
                                    ]
                                ]
                            ]
                        ),
                        'regular_price' => array_merge_recursive(
                            $priceWithAutoValueStructure,
                            [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'label' => __('Regular'),
                                            'dataScope' => 'regular_price',
                                            'sortOrder' => 50
                                        ]
                                    ]
                                ]
                            ]
                        ),
                        SubscriptionOptionInterface::IS_INSTALLMENTS_MODE => [
                            'arguments' => [
                                'data' => [
                                    'options' => $this->yesNoSource->toOptionArray(),
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'dataType' => Boolean::NAME,
                                        'componentType' => Field::NAME,
                                        'label' => __('Installments Mode'),
                                        'dataScope' => SubscriptionOptionInterface::IS_INSTALLMENTS_MODE,
                                        'sortOrder' => 60,
                                        'tooltip' => [
                                            'description' => __($installmentsModeHint),
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => '',
                                        'sortOrder' => 100
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get website Ids select structure
     *
     * @return array
     */
    private function getWebsiteIdStructure()
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();

        $isSingleStore = $this->storeManager->isSingleStoreMode();
        $isShowWebsite = !$isSingleStore;
        $isAllowChange = !(!$isShowWebsite || $product->getStoreId());
        $default = $isShowWebsite && !$isAllowChange
            ? $this->storeManager->getStore($product->getStoreId())->getWebsiteId()
            : 0;

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'dataType' => Text::NAME,
                        'formElement' => Select::NAME,
                        'componentType' => Field::NAME,
                        'component' => 'Aheadworks_Sarp2/js/ui/form/element/product/website-select',
                        'label' => __('Website'),
                        'options' => $this->websiteSource->toOptionArray(),
                        'value' => $default,
                        'visible' => !$isSingleStore,
                        'disabled' => ($isShowWebsite && !$isAllowChange)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get no subscription options notice structure
     *
     * @return array
     */
    private function getNoOptionsNoticeStructure()
    {
        return [
            'arguments' => [
                'block' => $this->blockFactory->createBlock(
                    NoticeBlock::class,
                    [
                        'data' => [
                            'template' => 'Aheadworks_Sarp2::product/subscription_options/notice/no_options.phtml'
                        ]
                    ]
                ),
                'data' => [
                    'config' => [
                        'componentType' => 'htmlContent',
                        'required' => false
                    ]
                ]
            ]
        ];
    }

    /**
     * Get notice structure about child configuration
     *
     * @return array
     */
    private function getChildConfigNoticeStructure()
    {
        return [
            self::SUBSCRIPTION_OPTIONS_ATTR_CODE . '_child_config_notice' => [
                'arguments' => [
                    'block' => $this->blockFactory->createBlock(
                        NoticeBlock::class,
                        [
                            'data' => [
                                'template' => 'Aheadworks_Sarp2::product/subscription_options/notice/'
                                    . 'child_config.phtml'
                            ]
                        ]
                    ),
                    'data' => [
                        'config' => [
                            'componentType' => 'htmlContent',
                            'required' => false,
                            'additionalClasses' => 'admin__field'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        if (isset($data[$productId][static::DATA_SOURCE_DEFAULT][self::SUBSCRIPTION_OPTIONS_ATTR_CODE])) {
            foreach ($data[$productId][static::DATA_SOURCE_DEFAULT][self::SUBSCRIPTION_OPTIONS_ATTR_CODE] as &$option) {
                $option[SubscriptionOptionInterface::INITIAL_FEE] = $this->formatPrice(
                    $option[SubscriptionOptionInterface::INITIAL_FEE]
                );
                $option[SubscriptionOptionInterface::TRIAL_PRICE] = $this->formatPrice(
                    $option[SubscriptionOptionInterface::TRIAL_PRICE]
                );
                $option[SubscriptionOptionInterface::REGULAR_PRICE] = $this->formatPrice(
                    $option[SubscriptionOptionInterface::REGULAR_PRICE]
                );
            }
        }
        return $data;
    }

    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string|null
     */
    protected function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }
        try {
            $store = $this->locator->getStore();
            $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());
            $value = $currency->toCurrency($value, ['display' => Currency::NO_SYMBOL]);
        } catch (\Exception $e) {
            return null;
        }
        return $value;
    }
}
