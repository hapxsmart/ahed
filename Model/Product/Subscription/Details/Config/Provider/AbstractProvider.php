<?php
namespace Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\Provider;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Product\Subscription\Details\Config\ProcessorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class AbstractProvider
{
    /**
     * @var ProcessorInterface
     */
    private $processorComposite;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagement;

    /**
     * @var ConfigInterface[]
     */
    private $configComposite;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManagement
     * @param ProcessorInterface|null $processorComposite
     * @param array $configComposite
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManagement,
        ProcessorInterface $processorComposite = null,
        $configComposite = []
    ) {
        $this->productRepository = $productRepository;
        $this->storeManagement = $storeManagement;
        $this->processorComposite = $processorComposite;
        $this->configComposite = $configComposite;
    }

    /**
     * Get subscription details config for product
     *
     * @param int $productId
     * @param ProfileItemInterface|null $item
     * @return array
     */
    public function getConfig($productId, $item = null)
    {
        try {
            $product = $this->productRepository->getById($productId);
            $configArray = [
                'productType' => $product->getTypeId(),
                'productId' => $productId,
                'selectedSubscriptionOptionId' => null,
                'currencyFormat' => $this->storeManagement->getStore()->getCurrentCurrency()->getOutputFormat()
            ];
            foreach ($this->configComposite as $name => $config) {
                $configArray[$name] = $config->getConfig($product, $item);
            }
        } catch (LocalizedException $e) {
            $configArray = [];
        }

        if ($this->processorComposite) {
            $configArray = $this->processorComposite->process($configArray);
        }

        return $configArray;
    }

    /**
     * Get subscription details config
     *
     * @param int $productId
     * @param ProfileItemInterface|null $item
     * @param ProfileInterface $profile
     * @return array
     * @throws LocalizedException
     */
    public function getSubscriptionDetailsConfig($productId, $item = null, $profile = null)
    {
        $subscriptionDetailsConfig = $this->configComposite['subscriptionDetails'];
        $product = $this->productRepository->getById($productId);

        return $subscriptionDetailsConfig->getConfig($product, $item, $profile);
    }
}
