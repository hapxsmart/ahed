<?php
namespace Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\Provider\Decorator;

use Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\Provider as BaseProvider;
use Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\ProviderInterface;
use Aheadworks\Sarp2\ViewModel\Subscription\Details\ForQuoteItem as QuoteItemDetailsViewModel;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;

/**
 * Class PlanNameDecorator
 *
 * @package Aheadworks\Sarp2\Model\Sales\Quote\Item\Option\SubscriptionOptions\Provider\Decorator
 */
class PlanNameDecorator implements ProviderInterface
{
    /**
     * @var BaseProvider
     */
    private $wrappee;

    /**
     * @var QuoteItemDetailsViewModel
     */
    private $detailsViewModel;

    /**
     * PlanNameDecorator constructor.
     *
     * @param BaseProvider $wrappee
     * @param QuoteItemDetailsViewModel $itemDetailsViewModel
     */
    public function __construct(
        BaseProvider $wrappee,
        QuoteItemDetailsViewModel $itemDetailsViewModel
    ) {
        $this->wrappee = $wrappee;
        $this->detailsViewModel = $itemDetailsViewModel;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptionOptions(ItemInterface $item)
    {
        $planNameOption = [
            'label' => __('Subscription Plan'),
            'value' => $this->detailsViewModel->getPlanName($item)
        ];

        $options = $this->wrappee->getSubscriptionOptions($item);
        array_unshift($options, $planNameOption);

        return $options;
    }
}
