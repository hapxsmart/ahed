<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\Configurable;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Config;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider\ConfigInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Price\AsLowAsCalculator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;

class AsLowAs implements ConfigInterface
{
    /**
     * @var AsLowAsCalculator
     */
    private $asLowAsCalculator;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param AsLowAsCalculator $asLowAsCalculator
     * @param Config $config
     */
    public function __construct(
        AsLowAsCalculator $asLowAsCalculator,
        Config $config
    ) {
        $this->asLowAsCalculator = $asLowAsCalculator;
        $this->config = $config;
    }

    /**
     * Get as low as config
     *
     * @param ProductInterface $product
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface|null $profile
     * @return array|null
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getConfig($product, $item = null, $profile = null)
    {
        // todo: M2SARP2-990 Hide "As Low As" price in release 2.12
        return $this->config->isUsedSubscriptionPriceInAsLowAs()
            ? $this->asLowAsCalculator->calculate($product->getId())
            : null;
    }
}
