<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

/**
 * Interface OfflinePaymentRendererInterface
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
 */
interface OfflinePaymentRendererInterface
{
    /**
     * Render method details
     *
     * @param string $paymentMethodCode
     * @return string
     */
    public function render(string $paymentMethodCode);
}
