<?php
namespace Aheadworks\Sarp2\Api;

/**
 * Interface GuestPaymentInfoManagementInterface
 * @package Aheadworks\Sarp2\Api
 */
interface GuestPaymentInfoManagementInterface
{
    /**
     * Save payment information and submit cart
     *
     * @param string $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return bool
     */
    public function savePaymentInfoAndSubmitCart(
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    );
}
