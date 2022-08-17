<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

use Aheadworks\Sarp2\Api\Data\PaymentTokenInterface as Sarp2PaymentTokenInterface;

/**
 * Interface TokenRendererInterface
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
 */
interface TokenRendererInterface
{
    /**
     * Renders specified token
     *
     * @param Sarp2PaymentTokenInterface $token
     * @return string
     */
    public function render($token);
}
