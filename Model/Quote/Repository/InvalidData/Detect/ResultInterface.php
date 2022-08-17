<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect;

/**
 * Interface ResultInterface
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Detect
 */
interface ResultInterface
{
    /**#@+
     * Reason types
     */
    const REASON_COUPON_ON_SUBSCRIPTION_CART = 1;
    const REASON_COUPON_ON_MIXED_CART = 2;
    const REASON_EE_GIFT_CARD_ON_SUBSCRIPTION_CART = 3;
    const REASON_EE_GIFT_CARD_ON_MIXED_CART = 4;
    const REASON_AW_GIFT_CARD_ON_SUBSCRIPTION_CART = 5;
    const REASON_AW_GIFT_CARD_ON_MIXED_CART = 6;
    /**#@-*/

    /**
     * Check if data invalid
     *
     * @return bool
     */
    public function isInvalid();

    /**
     * Get reason of invalid data
     *
     * @return int|null
     */
    public function getReason();

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage();
}
