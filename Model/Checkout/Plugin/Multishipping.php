<?php
namespace Aheadworks\Sarp2\Model\Checkout\Plugin;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Multishipping\Helper\Data as MultishippingHelper;

/**
 * Class Multishipping
 * @package Aheadworks\Sarp2\Model\Checkout\Plugin
 */
class Multishipping
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        CheckoutSession $checkoutSession
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param MultishippingHelper $subject
     * @param \Closure $proceed
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsMultishippingCheckoutAvailable(
        MultishippingHelper $subject,
        \Closure $proceed
    ) {
        $quote = $this->checkoutSession->getQuote();
        return $this->quoteChecker->check($quote)
            ? false
            : $proceed();
    }
}
