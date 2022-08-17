<?php
namespace Aheadworks\Sarp2\Plugin\Product\Configuration;

use Aheadworks\Sarp2\Model\Quote\Item\Checker\IsSubscription;
use Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\Provider as OptionProvider;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class Generic
 *
 * @package Aheadworks\Sarp2\Plugin\Product\Configuration\Generic
 */
class Generic
{
    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * @param IsSubscription $isSubscriptionChecker
     * @param OptionProvider $optionProvider
     */
    public function __construct(
        IsSubscription $isSubscriptionChecker,
        OptionProvider $optionProvider
    ) {
        $this->isSubscriptionChecker = $isSubscriptionChecker;
        $this->optionProvider = $optionProvider;
    }

    /**
     * @param ProductConfiguration $subject
     * @param array $options
     * @param ItemInterface $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCustomOptions(ProductConfiguration $subject, array $options, ItemInterface $item)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        return $this->isSubscriptionChecker->check($item)
            ? array_merge($options, $this->optionProvider->getSubscriptionOptions($item))
            : $options;
    }
}
