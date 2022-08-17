<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails;

use Magento\Framework\View\Element\Template;

/**
 * Class AbstractTokenRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails
 */
abstract class AbstractTokenRenderer extends Template implements TokenRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($token)
    {
        $this->assign('token', $token);
        $this->assign('tokenDetails', $token->getDetails());
        return $this->toHtml();
    }
}
