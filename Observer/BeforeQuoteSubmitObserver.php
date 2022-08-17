<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Quote\Checker\HasPaymentMethod;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class BeforeQuoteSubmitObserver
 * @package Aheadworks\Sarp2\Observer
 */
class BeforeQuoteSubmitObserver implements ObserverInterface
{
    /**
     * @var HasPaymentMethod
     */
    private $quoteChecker;

    /**
     * @param HasPaymentMethod $quoteChecker
     */
    public function __construct(HasPaymentMethod $quoteChecker)
    {
        $this->quoteChecker = $quoteChecker;
    }

    /**
     * Disable send email notification if free method
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($this->quoteChecker->checkFreePayment($quote)) {
            $order->setCanSendNewEmailFlag(false);
        }
    }
}
