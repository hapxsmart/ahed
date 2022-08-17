<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface ConfigInterface
{
    /**
     * Get subscription config option for product
     *
     * @param ProductInterface $product
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return mixed
     */
    public function getConfig($product, $item = null, $profile = null);
}
