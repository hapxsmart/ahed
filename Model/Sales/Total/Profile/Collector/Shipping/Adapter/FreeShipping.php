<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Adapter;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Quote\Substitute\Quote\Address as AddressSubstitute;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;

/**
 * Class FreeShipping
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Collector\Shipping\Adapter
 */
class FreeShipping
{
    /**
     * @var FreeShippingInterface
     */
    private $freeShipping;

    /**
     * @param FreeShippingInterface $freeShipping
     */
    public function __construct(FreeShippingInterface $freeShipping)
    {
        $this->freeShipping = $freeShipping;
    }

    /**
     * Check if free shipping available
     *
     * @param AddressSubstitute $addressSubstitute
     * @return bool
     */
    public function isFreeShipping($addressSubstitute)
    {
        $quoteSubstitute = $addressSubstitute->getQuote();
        $quoteSubstitute->setShippingAddress($addressSubstitute);

        $items = $addressSubstitute->getAllItems();
        foreach ($items as $item) {
            // Enable free shipping validation in cart price rules
            $item->getProduct()->setForceValidate(true);
        }
        $result = $this->freeShipping->isFreeShipping($addressSubstitute->getQuote(), $items);

        foreach ($items as $item) {
            /** @var ProfileItemInterface $profileItem */
            $profileItem = $item->getLinkedProfileItem();
            if ($profileItem) {
                $profileItem->setIsFreeShipping((bool)$item->getFreeShipping());
            }
        }

        return $result;
    }
}
