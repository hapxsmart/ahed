<?php
namespace Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type;

use Aheadworks\Sarp2\Api\SubscriptionOptionRepositoryInterface;
use Aheadworks\Sarp2\Model\Product\Attribute\Source\SubscriptionType;
use Aheadworks\Sarp2\Model\Product\Type\Plugin\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;

/**
 * Interface Generic
 * @package Aheadworks\Sarp2\Model\Product\Checker\IsSubscription\Type
 */
class Generic implements HandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $typeConfig;

    /**
     * @var SubscriptionOptionRepositoryInterface
     */
    private $optionsRepository;

    /**
     * @param ConfigInterface $typeConfig
     * @param SubscriptionOptionRepositoryInterface $optionsRepository
     */
    public function __construct(
        ConfigInterface $typeConfig,
        SubscriptionOptionRepositoryInterface $optionsRepository
    ) {
        $this->typeConfig = $typeConfig;
        $this->optionsRepository = $optionsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function check($product, $subscriptionOnly = false)
    {
        $typeId = $product->getTypeId();
        $supportedTypes = $this->typeConfig->filter(Config::SUPPORTED_CUSTOM_ATTR_CODE, true);
        if (!in_array($typeId, $supportedTypes)) {
            return false;
        }

        /** @var Product $product */
        $subscriptionType = (int)$product->getData('aw_sarp2_subscription_type');
        if ($subscriptionType == SubscriptionType::NO
            || $subscriptionOnly && $subscriptionType != SubscriptionType::SUBSCRIPTION_ONLY
        ) {
            return false;
        }

        $subscriptionOptions = $this->optionsRepository->getList($product->getId());
        if (!count($subscriptionOptions)) {
            return false;
        }

        return true;
    }
}
