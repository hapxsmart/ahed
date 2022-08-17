<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;
use Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider\Composite\ThirdPartyConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class PaymentConfig
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Edit\Payment\ConfigProvider
 */
class PaymentConfig implements ConfigProviderInterface
{
    /**
     * @var ThirdPartyConfigProvider
     */
    private $thirdPartConfigProvider;

    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param IntegratedMethodList $integratedMethodList
     * @param ThirdPartyConfigProvider $thirdPartConfigProvider
     */
    public function __construct(
        IntegratedMethodList $integratedMethodList,
        ThirdPartyConfigProvider $thirdPartConfigProvider
    ) {
        $this->integratedMethodList = $integratedMethodList;
        $this->thirdPartConfigProvider = $thirdPartConfigProvider;
    }

    /**
     * Return configuration array
     *
     * @return array
     */
    public function getConfig()
    {
        $config = $this->iframeConfig();

        foreach ($this->integratedMethodList->getList() as $integratedMethod) {
            if ($integratedMethod->isEnablePaymentModule()) {
                $config = array_merge_recursive($config, $integratedMethod->getProcessedConfig());
            }
        }

        //todo: begin legacy mechanism, need replace to IntegratedMethodPool
        $thirdPartyConfigProviders = $this->thirdPartConfigProvider->getConfigProviders();
        foreach ($thirdPartyConfigProviders as $configProvider) {
            $config = array_merge_recursive($config, $configProvider->getConfig());
        }
        //todo: end legacy mechanism

        return $config;
    }

    /**
     * Default checkout iframe form config preset
     *
     * @return array
     */
    private function iframeConfig()
    {
        return [
            'payment' => [
                'iframe' => [
                    'timeoutTime' => [],
                    'dateDelim' => [],
                    'cardFieldsMap' => [],
                    'source' =>  [],
                    'controllerName' => [],
                    'cgiUrl' => [],
                    'placeOrderUrl' => [],
                    'saveOrderUrl' => [],
                    'expireYearLength' => []
                ]
            ]
        ];
    }
}
