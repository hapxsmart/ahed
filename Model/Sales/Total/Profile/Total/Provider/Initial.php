<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider
 */
class Initial extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getUnitPrice($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseInitialPrice()
            : $item->getInitialPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPriceInclTax($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseInitialPriceInclTax()
            : $item->getInitialPriceInclTax();
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAddressSubtotal($address, $useBaseCurrency)
    {
        $result = 0.0;
        if ($this->hasData('profile')) {
            /** @var ProfileInterface $profile */
            $profile = $this->getData('profile');
            $result = $useBaseCurrency
                ? $profile->getBaseInitialSubtotal()
                : $profile->getInitialSubtotal();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAddressSubtotalInclTax($address, $useBaseCurrency)
    {
        $result = 0.0;
        if ($this->hasData('profile')) {
            /** @var ProfileInterface $profile */
            $profile = $this->getData('profile');
            $result = $useBaseCurrency
                ? $profile->getBaseInitialSubtotalInclTax()
                : $profile->getInitialSubtotalInclTax();
        }
        return $result;
    }
}
