<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class Regular
 * @package Aheadworks\Sarp2\Model\Sales\Total\Profile\Total\Provider
 */
class Regular extends AbstractProvider
{
    /**
     * {@inheritdoc}
     */
    public function getUnitPrice($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseRegularPrice()
            : $item->getRegularPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPriceInclTax($item, $useBaseCurrency)
    {
        return $useBaseCurrency
            ? $item->getBaseRegularPriceInclTax()
            : $item->getRegularPriceInclTax();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getShippingAmount($address, $useBaseCurrency)
    {
        $result = 0.0;
        if ($this->hasData('profile')) {
            /** @var ProfileInterface $profile */
            $profile = $this->getData('profile');
            $result = $useBaseCurrency
                ? $profile->getBaseRegularShippingAmount()
                : $profile->getRegularShippingAmount();
        }
        return $result;
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
                ? $profile->getBaseRegularSubtotal()
                : $profile->getRegularSubtotal();
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
                ? $profile->getBaseRegularSubtotalInclTax()
                : $profile->getRegularSubtotalInclTax();
        }
        return $result;
    }
}
