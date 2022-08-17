<?php
namespace Aheadworks\Sarp2\Observer\Bundle;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class ConvertQuoteItemToProfileItemObserver
 *
 * @package Aheadworks\Sarp2\Observer\Bundle
 */
class ConvertQuoteItemToProfileItemObserver implements ObserverInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var QuoteItem $quoteItem */
        $quoteItem = $event->getData('quote_item');
        /** @var ProfileItemInterface $profileItem */
        $profileItem = $event->getData('profile_item');

        if ($attributes = $quoteItem->getProduct()->getCustomOption('bundle_selection_attributes')) {
            $productOptions = $profileItem->getProductOptions();
            $productOptions['bundle_selection_attributes'] = $attributes->getValue();
            $profileItem->setProductOptions($productOptions);
        }
    }
}
