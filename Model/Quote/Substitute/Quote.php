<?php
namespace Aheadworks\Sarp2\Model\Quote\Substitute;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote as QuoteModel;

/**
 * Class Quote
 * @package Aheadworks\Sarp2\Model\Quote\Substitute
 */
class Quote extends QuoteModel
{
    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->getData('store');
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingAddress()
    {
        return $this->getData('shipping_address');
    }

    /**
     * {@inheritdoc}
     */
    public function setShippingAddress(AddressInterface $address = null)
    {
        return $this->setData('shipping_address', $address);
    }
}
