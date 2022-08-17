<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\AbstractTokenWithIconRenderer;

/**
 * Class BaseCreditCardRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class DefaultTokenWithIconRenderer extends AbstractTokenWithIconRenderer
{
    /**
     * @var string
     */
    protected $_template =
        'Aheadworks_Sarp2::customer/subscriptions/edit/view/payment_details/type/token_with_icon.phtml';
}
