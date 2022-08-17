<?php
namespace Aheadworks\Sarp2\Api;

/**
 * Interface PaymentInfoManagementInterface
 * @package Aheadworks\Sarp2\Api
 */
interface PaymentInfoManagementInterface
{
    /**
     * Save payment information and submit cart
     *
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return bool
     */
    public function savePaymentInfoAndSubmitCart(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );

    /**
     * Get payment info allowed for subscription
     *
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function getPaymentInfoForSubscription();
}
