<?php
namespace Aheadworks\Sarp2\Model\Quote\Substitute\Quote;

use Magento\Quote\Model\Quote\Address as QuoteAddress;

/**
 * Class Address
 * @package Aheadworks\Sarp2\Model\Quote\Substitute\Quote
 */
class Address extends QuoteAddress
{
    /**
     * {@inheritdoc}
     */
    public function getAllItems()
    {
        return $this->getData('all_items');
    }

    /**
     * {@inheritdoc}
     */
    public function getQuote()
    {
        return $this->getData('quote');
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseSubtotalWithDiscount()
    {
        return $this->getData('base_subtotal_with_discount');
    }
}
