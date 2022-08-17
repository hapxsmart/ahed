<?php
namespace Aheadworks\Sarp2\Model\Checkout\ConfigProvider;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session;

/**
 * Class MixedPaymentMethodList
 *
 * @package Aheadworks\Sarp2\Model\Checkout\ConfigProvider
 */
class MixedPaymentMethodList implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    private $mixedMethodList = [
        'aw_bambora_apac',
        'aw_nmi',
        'authorizenet_acceptjs',
        'cashondelivery'
    ];

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param Session $checkoutSession
     * @param IntegratedMethodList $integratedMethodList
     * @param array $mixedMethodList
     */
    public function __construct(
        Session $checkoutSession,
        IntegratedMethodList $integratedMethodList,
        array $mixedMethodList = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->integratedMethodList = $integratedMethodList;
        $this->mixedMethodList = array_merge($this->mixedMethodList, $mixedMethodList);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $mixedPaymentList = $this->mixedMethodList;
            foreach ($this->integratedMethodList->getList() as $integratedMethod) {
                $mixedPaymentList[] = $integratedMethod->getCode();
            }
            $config['awSarp2MixedPaymentMethodList'] = $mixedPaymentList;
        }
        return $config;
    }
}
