<?php
namespace Aheadworks\Sarp2\Plugin\Sales\Service;

use Magento\Sales\Model\Service\PaymentFailuresService;

/**
 * Class PaymentFailuresServicePlugin
 *
 * @package Aheadworks\Sarp2\Plugin\Sales\Service
 */
class PaymentFailuresServicePlugin
{
    /**
     * @param PaymentFailuresService $subject
     * @param callable $proceed
     * @param int $cartId
     * @param string $message
     * @param string $checkoutType
     * @return PaymentFailuresService
     */
    public function aroundHandle(
        PaymentFailuresService $subject,
        callable $proceed,
        int $cartId,
        string $message,
        string $checkoutType = 'onepage'
    ) {
        if ($cartId) {
            return $proceed($cartId, $message, $checkoutType);
        } else {
            return $subject;
        }
    }
}
