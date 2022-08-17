<?php
namespace Aheadworks\Sarp2\Model\Payment\Paypal\Plugin;

use Aheadworks\Sarp2\Model\Product\Checker\IsSubscription;
use Magento\Framework\Registry;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Paypal\Observer\AddPaypalShortcutsObserver;

/**
 * Class PaypalShortcuts
 * @package Aheadworks\Sarp2\Model\Payment\Paypal\Plugin
 */
class PaypalShortcuts
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var IsSubscription
     */
    private $isSubscriptionChecker;

    /**
     * @param Registry $registry
     * @param IsSubscription $isSubscriptionChecker
     */
    public function __construct(
        Registry $registry,
        IsSubscription $isSubscriptionChecker
    ) {
        $this->registry = $registry;
        $this->isSubscriptionChecker = $isSubscriptionChecker;
    }

    /**
     * @param AddPaypalShortcutsObserver $subject
     * @param \Closure $proceed
     * @param EventObserver $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        AddPaypalShortcutsObserver $subject,
        \Closure $proceed,
        EventObserver $observer
    ) {
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct && $this->isSubscriptionChecker->check($currentProduct)) {
            return;
        }
        return $proceed($observer);
    }
}
