<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Model\Quote\Checker\HasSubscriptions;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\Method\Free;
use Magento\Quote\Model\Quote;

class PaymentMethodAvailabilityObserver implements ObserverInterface
{
    /**
     * @var HasSubscriptions
     */
    private $quoteChecker;

    /**
     * @var array
     */
    private $notAllowedPayments;

    /**
     * @param HasSubscriptions $quoteChecker
     * @param array $notAllowedPayments
     */
    public function __construct(
        HasSubscriptions $quoteChecker,
        $notAllowedPayments = []
    ) {
        $this->quoteChecker = $quoteChecker;
        $this->notAllowedPayments = $notAllowedPayments;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var DataObject $result */
        $result = $event->getResult();
        /** @var Quote $quote */
        $quote = $event->getQuote();

        if ($quote) {
            /** @var MethodInterface $methodInstance */
            $methodInstance = $event->getMethodInstance();

            $canUseMixedCheckout = (bool) $methodInstance->getConfigData('aw_sarp_can_use_mixed_checkout');
            $canUseSubscriptionCheckout = (bool) $methodInstance->getConfigData(
                'aw_sarp_can_use_subscription_checkout'
            );

            if ($quote
                && (!$canUseMixedCheckout && $this->quoteChecker->checkHasBoth($quote)
                    || !$canUseSubscriptionCheckout && $this->quoteChecker->checkHasSubscriptionsOnly($quote))
                || ($quote->hasData('aw_sarp_get_recurring_payments_flag') && !$canUseSubscriptionCheckout)
            ) {
                $result->setData('is_available', false);
            }

            if ($quote->getGrandTotal() <= 0) {
                if ($quote->hasData('aw_sarp_allow_free_payment_method')
                    && $methodInstance->getCode() == Free::PAYMENT_METHOD_FREE_CODE
                ) {
                    $result->setData('is_available', true);
                }
                if (isset($this->notAllowedPayments[$methodInstance->getCode()])) {
                    $result->setData('is_available', false);
                }
            }
        }

        return $this;
    }
}
