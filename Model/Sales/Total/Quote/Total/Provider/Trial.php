<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider;

use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;

/**
 * Class Trial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider
 */
class Trial implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUnitPrice($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpTrialPrice()
            : $item->getAwSarpTrialPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPriceInclTax($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpTrialPriceInclTax()
            : $item->getAwSarpTrialPriceInclTax();
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAmount($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpTrialShippingAmount()
            : $address->getAwSarpTrialShippingAmount();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotal($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpTrialSubtotal()
            : $address->getAwSarpTrialSubtotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotalInclTax($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpTrialSubtotalInclTax()
            : $address->getAwSarpTrialSubtotalInclTax();
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
