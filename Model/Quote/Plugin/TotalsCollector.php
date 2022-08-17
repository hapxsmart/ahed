<?php
namespace Aheadworks\Sarp2\Model\Quote\Plugin;

use Aheadworks\Sarp2\Model\Quote\Address\TotalsCollector as AddressTotalsCollector;
use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\TotalsCollector as QuoteTotalsCollector;

/**
 * Class TotalsCollector
 * @package Aheadworks\Sarp2\Model\Quote\Plugin
 */
class TotalsCollector
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var AddressTotalsCollector
     */
    private $addressTotalsCollector;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param AddressTotalsCollector $addressTotalsCollector
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        AddressTotalsCollector $addressTotalsCollector
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->addressTotalsCollector = $addressTotalsCollector;
    }

    /**
     * @param QuoteTotalsCollector $subject
     * @param \Closure $proceed
     * @param Quote $quote
     * @param Address$address
     * @return Total
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollectAddressTotals(
        QuoteTotalsCollector $subject,
        \Closure $proceed,
        $quote,
        $address
    ) {
        return $this->quoteChecker->check($quote)
            ? $this->addressTotalsCollector->collect($quote, $address)
            : $proceed($quote, $address);
    }
}
