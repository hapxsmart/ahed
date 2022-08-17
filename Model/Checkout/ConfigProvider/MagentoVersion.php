<?php
namespace Aheadworks\Sarp2\Model\Checkout\ConfigProvider;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class MagentoVersion
 * @package Aheadworks\Sarp2\Model\Checkout\ConfigProvider
 */
class MagentoVersion implements ConfigProviderInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'magentoVersion' => $this->productMetadata->getVersion()
        ];
    }
}
