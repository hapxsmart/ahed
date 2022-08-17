<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProviderPool as ConfigProviderPool;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsOneOffItem;
use Aheadworks\Sarp2\Model\Profile\Registry;
use Aheadworks\Sarp2\Model\UrlBuilder;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View as ProductViewBlock;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\FormatInterface;
use Aheadworks\Sarp2\Model\Directory\PriceCurrency;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Url\EncoderInterface;

/**
 * Class ProductItem
 */
class ProductItem extends ProductViewBlock
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ConfigProviderPool
     */
    private $configProviderPool;

    /**
     * @var IsOneOffItem
     */
    private $isOneOffItem;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param StringUtils $string
     * @param Product $productHelper
     * @param ConfigInterface $productTypeConfig
     * @param FormatInterface $localeFormat
     * @param Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param PriceCurrency $priceCurrency
     * @param Registry $registry
     * @param ConfigProviderPool $configProviderPool
     * @param IsOneOffItem $isOneOffItem
     * @param UrlBuilder $urlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        StringUtils $string,
        Product $productHelper,
        ConfigInterface $productTypeConfig,
        FormatInterface $localeFormat,
        Session $customerSession,
        ProductRepositoryInterface $productRepository,
        PriceCurrency $priceCurrency,
        Registry $registry,
        ConfigProviderPool $configProviderPool,
        IsOneOffItem $isOneOffItem,
        UrlBuilder $urlBuilder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );
        $this->registry = $registry;
        $this->configProviderPool = $configProviderPool;
        $this->isOneOffItem = $isOneOffItem;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve profile item
     *
     * @return ProfileItemInterface
     */
    public function getProfileItem()
    {
        return $this->registry->getProfileItem();
    }

    /**
     * Retrieve item qty
     *
     * @return int|null
     */
    public function getQty()
    {
        return $this->getProduct()->getPreconfiguredValues()->hasData('qty')
            ? $this->getProduct()->getPreconfiguredValues()->getData('qty')
            : null;
    }

    /**
     * Retrieve item configurable options
     *
     * @return array
     */
    public function getConfigurableOptions()
    {
        return $this->getProduct()->getPreconfiguredValues()->hasData('super_attribute')
            ? $this->getProduct()->getPreconfiguredValues()->getData('super_attribute')
            : [];
    }

    /**
     * Retrieve item configurable options as json
     * @return bool|false|string
     */
    public function getSerializedConfigurableOptions()
    {
        return $this->_jsonEncoder->encode($this->getConfigurableOptions());
    }

    /**
     * Get subscription config data
     *
     * @return bool|false|string
     */
    public function getSubscriptionConfigData()
    {
        try {
            $product = $this->getProduct();
            $productTypeId = $product->getTypeId();
            $item = $productTypeId != BundleType::TYPE_CODE
                ? $this->getProfileItem()
                : null;
            $configData = $this->configProviderPool->getConfigProvider($productTypeId)
                ->getConfig($product->getId(), $item);

            return $this->_jsonEncoder->encode($configData);
        } catch (\Exception $exception) {
            return $this->_jsonEncoder->encode([]);
        }
    }

    /**
     * Retrieve subscription option id
     *
     * @return int
     */
    public function getSubscriptionOptionId()
    {
        $item = $this->getProfileItem();
        if ($item instanceof ProfileItemInterface) {
            $options = $item->getProductOptions();
            return $options['info_buyRequest']['aw_sarp2_subscription_type'] ?? 0;
        }

        return 0;
    }

    /**
     * Check if current item is one-off
     *
     * @return bool
     */
    public function isOneOffItem(): bool
    {
        $item = $this->getProfileItem();

        return $this->isOneOffItem->check($item);
    }

    /**
     * @inheritDoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $block = $this->getLayout()->getBlock('product.info');
        if ($block) {
            $request = $this->getRequest();
            $itemId = $request->getParam(ProfileItemInterface::ITEM_ID);
            $profileId = $request->getParam(ProfileInterface::PROFILE_ID);

            $block->setSubmitRouteData(
                [
                    'route' => 'aw_sarp2/profile_edit/saveItem',
                    'params' => $this->urlBuilder->getParams($profileId, $request, $itemId),
                ]
            );
        }

        return parent::_prepareLayout();
    }
}
