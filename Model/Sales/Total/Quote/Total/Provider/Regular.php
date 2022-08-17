<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider;

use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;

/**
 * Class Regular
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider
 */
class Regular implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUnitPrice($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpRegularPrice()
            : $item->getAwSarpRegularPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPriceInclTax($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpRegularPriceInclTax()
            : $item->getAwSarpRegularPriceInclTax();
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAmount($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpRegularShippingAmount()
            : $address->getAwSarpRegularShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotal($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpRegularSubtotal()
            : $address->getAwSarpRegularSubtotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotalInclTax($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpRegularSubtotalInclTax()
            : $address->getAwSarpRegularSubtotalInclTax();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDiscountAmount($item, $useBaseCurrency)
    {
        return 0;
    }
}
