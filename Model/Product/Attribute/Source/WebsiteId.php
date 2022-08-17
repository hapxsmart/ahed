<?php
namespace Aheadworks\Sarp2\Model\Product\Attribute\Source;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Phrase;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

/**
 * Class WebsiteId
 * @package Aheadworks\Sarp2\Model\Product\Attribute\Source
 */
class WebsiteId implements OptionSourceInterface
{
    /**
     * @var DirectoryHelper
     */
    private $directoryHelper;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $options;

    /**
     * @param DirectoryHelper $directoryHelper
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        DirectoryHelper $directoryHelper,
        LocatorInterface $locator,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->locator = $locator;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = [
                $this->getOptionItem(__('All Websites'), 0, $this->directoryHelper->getBaseCurrencyCode())
            ];

            /** @var Product $product */
            $product = $this->getProduct();
            if ($product && $product->getStoreId()) {
                /** @var Store $store */
                $store = $this->locator->getStore();
                $this->options[] = $this->getWebsiteOptionItem($store->getWebsite());
            } elseif ($product) {
                $productWebsiteIds = $product->getWebsiteIds();
                $this->options = array_merge($this->options, $this->getWebsiteOptions($productWebsiteIds));
            } else {
                $this->options = array_merge($this->options, $this->getWebsiteOptions());
            }
        }
        return $this->options;
    }

    private function getWebsiteOptions($productWebsiteIds = [])
    {
        $options = [];
        /** @var Website $website */
        foreach ($this->storeManager->getWebsites() as $website) {
            if (empty($productWebsiteIds) || in_array($website->getId(), $productWebsiteIds)) {
                $options[] = $this->getWebsiteOptionItem($website);
            }
        }
        return $options;
    }

    /**
     * Retrieve product
     *
     * @return Product|ProductInterface|null
     */
    private function getProduct()
    {
        try {
            $product = $this->locator->getProduct();
        } catch (\Exception $e) {
            $product = null;
        }
        return $product;
    }

    /**
     * Get website option item
     *
     * @param Website $website
     * @return array
     */
    private function getWebsiteOptionItem($website)
    {
        return $this->getOptionItem(
            $website->getName(),
            $website->getId(),
            $website->getBaseCurrencyCode()
        );
    }

    /**
     * Get option item
     *
     * @param string|Phrase $title
     * @param int $value
     * @param string $currencyCode
     * @return array
     */
    private function getOptionItem($title, $value, $currencyCode)
    {
        return [
            'label' => $title . ' [' . $currencyCode . ']',
            'value' => $value
        ];
    }
}
