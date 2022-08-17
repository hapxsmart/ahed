<?php
namespace Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type;

use Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\OfflinePaymentRendererInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Helper\Data as PaymentData;

/**
 * Class DefaultOfflinePaymentRenderer
 *
 * @package Aheadworks\Sarp2\Block\Customer\Subscriptions\Edit\View\PaymentDetails\Type
 */
class DefaultOfflinePaymentRenderer extends Template implements OfflinePaymentRendererInterface
{
    /**
     * @var string
     */
    protected $_template =
        'Aheadworks_Sarp2::customer/subscriptions/edit/view/payment_details/type/offline_payment.phtml';

    /**
     * @var PaymentData
     */
    private $paymentData;

    /**
     * @param Context $context
     * @param PaymentData $paymentData
     * @param array $data
     */
    public function __construct(
        Context $context,
        PaymentData $paymentData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentData = $paymentData;
    }

    /**
     * @inheritDoc
     */
    public function render(string $paymentMethodCode)
    {
        try {
            $paymentMethod = $this->paymentData->getMethodInstance($paymentMethodCode);
            $this->assign('paymentMethod', $paymentMethod);
            $html = $this->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }
}
