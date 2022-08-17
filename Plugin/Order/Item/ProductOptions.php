<?php
namespace Aheadworks\Sarp2\Plugin\Order\Item;

use Aheadworks\Sarp2\Model\Sales\Order\Item\Option\ProcessorComposite;
use Magento\Sales\Model\Order\Item as OrderItem;

/**
 * Class ProductOptions
 *
 * @package Aheadworks\Sarp2\Plugin\Order\Item
 */
class ProductOptions
{
    /**
     * @var ProcessorComposite
     */
    private $processorComposite;

    /**
     * @param ProcessorComposite $processorComposite
     */
    public function __construct(
        ProcessorComposite $processorComposite
    ) {
        $this->processorComposite = $processorComposite;
    }

    /**
     * @param OrderItem $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductOptions(OrderItem $subject, array $options)
    {
        if ($this->isSubscription($options)) {
            $options = $this->processorComposite->process($subject, $options);
        }

        return $options;
    }

    /**
     * Check if an order item is a subscription
     *
     * @param array $options
     * @return bool
     */
    private function isSubscription($options)
    {
        if (isset($options['aw_sarp2_subscription_plan'])
            && is_array($options['aw_sarp2_subscription_plan'])
        ) {
            return true;
        }

        return false;
    }
}
