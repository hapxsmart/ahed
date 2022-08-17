<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider;

use Aheadworks\Sarp2\Model\Sales\Total\ProviderInterface;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Quote\Total\Provider
 */
class Initial implements ProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getUnitPrice($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpInitialFee()
            : $item->getAwSarpInitialFee();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPriceInclTax($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseAwSarpInitialFeeInclTax()
            : $item->getAwSarpInitialFeeInclTax();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getShippingAmount($address, $useBaseCurrency)
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotal($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpInitialSubtotal()
            : $address->getAwSarpInitialSubtotal();
    }

    /**
     * {@inheritdoc}
     */
    public function getAddressSubtotalInclTax($address, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $address->getBaseAwSarpInitialSubtotalInclTax()
            : $address->getAwSarpInitialSubtotalInclTax();
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
