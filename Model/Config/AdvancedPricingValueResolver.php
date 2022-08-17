<?php
namespace Aheadworks\Sarp2\Model\Config;

use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Product\Attribute\AttributeName;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * Class AdvancedPricingValueResolver
 *
 * @package Aheadworks\Sarp2\Model\Config
 */
class AdvancedPricingValueResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(Config $config, ProductRepositoryInterface $productRepository)
    {
        $this->config = $config;
        $this->productRepository = $productRepository;
    }

    /**
     * Retrieve boolean flag that determines the types of prices used
     *
     * @param int $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isUsedAdvancePricing($productId)
    {
        $product = $this->productRepository->getById($productId);

        $isUsedAdvancedPricing = $product->getData(AttributeName::AW_SARP2_IS_USED_ADVANCED_PRICING);
        if (Boolean::VALUE_USE_CONFIG == $isUsedAdvancedPricing
            || null == $isUsedAdvancedPricing
        ) {
            $isUsedAdvancedPricing = $this->config->isUsedAdvancedPricing();
        }

        return (bool)$isUsedAdvancedPricing;
    }
}
