<?php
namespace Aheadworks\Sarp2\Block\Product;

use Aheadworks\Sarp2\Block\Product\SubscriptionOptions\Renderer\AbstractRenderer;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProviderPool as ConfigProviderPool;
use Aheadworks\Sarp2\Model\Product\Subscription\Option\Source\Frontend as SubscriptionOptionSource;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\RendererList;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SubscriptionOptions
 * @package Aheadworks\Sarp2\Block\Product
 */
class SubscriptionOptions extends Template
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var SubscriptionOptionSource
     */
    private $optionSource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * todo: consider move all 'configs' into \Aheadworks\Sarp2\Api\Data\SubscriptionOptionInterface
     *       or another data interface. This will make available it on Web API layer
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $optionsArray;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * {@inheritdoc}
     */
    protected $_template = 'product/subscription_options.phtml';

    /**
     * @param Context $context
     * @param IsSubscription $isSubscriptionChecker
     * @param SubscriptionOptionSource $optionSource
     * @param ProductRepositoryInterface $productRepository
     * @param ConfigProviderPool $configProviderPool
     * @param Registry $registry
     * @param Config $config
     * @param JsonSerializer $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        IsSubscription $isSubscriptionChecker,
        SubscriptionOptionSource $optionSource,
        ProductRepositoryInterface $productRepository,
        ConfigProviderPool $configProviderPool,
        Registry $registry,
        Config $config,
        JsonSerializer $serializer,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionSource = $optionSource;
        $this->productRepository = $productRepository;
        $this->configProviderPool = $configProviderPool;
        $this->registry = $registry;
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * Get subscription option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        if ($this->optionsArray === null) {
            $productId = $this->getProductId();
            $this->optionsArray = $this->optionSource->getOptionArray($productId);
        }

        return $this->optionsArray;
    }

    /**
     * Get default option Id
     *
     * @return int
     */
    public function getDefaultOptionId()
    {
        if ($this->isSubscriptionChecker->checkById($this->getProductId(), true)) {
            $optionArray = $this->getOptionArray();
            $optionIds = array_keys($optionArray);
            return $optionIds[0];
        }
        return 0;
    }

    /**
     * Get config data
     *
     * @return array
     * @throws \Exception
     */
    public function getConfigData()
    {
        $productTypeId = $this->getProduct()->getTypeId();
        return $this->configProviderPool->getConfigProvider($productTypeId)
            ->getConfig($this->getProductId());
    }

    /**
     * Get selected option id from config
     *
     * @param array $config
     * @return int
     */
    public function getSelectedOptionId(array $config)
    {
        return $config['selectedSubscriptionOptionId'] ?? 0;
    }

    /**
     * Get product Id
     *
     * @return int|null
     */
    private function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * Get product
     *
     * @return ProductInterface|Product
     */
    private function getProduct()
    {
        return $this->registry->registry('product');
    }

    /**
     * {@inheritdoc}
     */
    public function toHtml()
    {
        if ($this->isSubscriptionChecker->checkById($this->getProductId())) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Check if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        /** @var ProductInterface $product */
        try {
            $product = $this->getProduct();
            $options = $product->getOptions();
            return $options && count($options) > 0;
        } catch (NoSuchEntityException $e) {
        }

        return false;
    }

    /**
     * Retrieve options list renderer block
     *
     * @return AbstractRenderer
     */
    public function getOptionsListRenderer()
    {
        $rendererName = $this->getNameInLayout() . '.' . $this->config->getSubscriptionOptionsRenderer();

        /** @var AbstractRenderer $block */
        $block = $this->getRenderer($rendererName);
        $block->setRenderedBlock($this);

        return $block;
    }

    /**
     * Retrieve options html
     *
     * @return string
     */
    public function getOptionsListHtml()
    {
        return $this->getOptionsListRenderer()->toHtml();
    }

    /**
     * Retrieve action renderer
     *
     * @param string $type
     * @return bool|AbstractRenderer
     */
    public function getRenderer($type)
    {
        /** @var RendererList $rendererList */
        $rendererList = $this->getChildBlock('renderer.list');
        if (!$rendererList) {
            throw new \RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }

        $renderer = $rendererList->getRenderer($type);

        return $renderer;
    }

    /**
     * Check if first option is No subscription
     *
     * @return bool
     */
    public function isFirstOptionNoPlan()
    {
        $optionsKeys = array_keys($this->getOptionArray());

        if (empty($optionsKeys)) {
            return false;
        }

        return (int)reset($optionsKeys) == 0;
    }

    /**
     * Serialize data to json string
     *
     * @param mixed $data
     * @return bool|false|string
     */
    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }
}
