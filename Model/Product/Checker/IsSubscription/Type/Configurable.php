<?php
namespace Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;

/**
 * Class Configurable
 * @package Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type
 */
class Configurable implements HandlerInterface
{
    /**
     * @var LinkManagementInterface
     */
    private $linkManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Generic
     */
    private $genericHandler;

    /**
     * @var ProductInterface[]
     */
    private $childProductsCache = [];

    /**
     * @param LinkManagementInterface $linkManagement
     * @param ProductRepositoryInterface $productRepository
     * @param Generic $genericHandler
     */
    public function __construct(
        LinkManagementInterface $linkManagement,
        ProductRepositoryInterface $productRepository,
        Generic $genericHandler
    ) {
        $this->linkManagement = $linkManagement;
        $this->productRepository = $productRepository;
        $this->genericHandler = $genericHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function check($product, $subscriptionOnly = false)
    {
        $childProducts = $this->getChildProducts($product->getData('sku'));
        foreach ($childProducts as $childProduct) {
            if ($this->genericHandler->check($childProduct, $subscriptionOnly)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve child products by sku
     *
     * @param string $sku
     * @return ProductInterface|ProductInterface[]
     */
    private function getChildProducts($sku)
    {
        if (!array_key_exists($sku, $this->childProductsCache)) {
            $this->childProductsCache[$sku] = $this->linkManagement->getChildren($sku);
        }

        return $this->childProductsCache[$sku];
    }
}
