<?php
namespace Aheadworks\Sarp2\Plugin\Payment\Helper;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodList;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Magento\Payment\Helper\Data;

/**
 * Class DataPlugin
 * @package Aheadworks\Sarp2\Plugin\Payment\Helper
 */
class DataPlugin
{
    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @var IntegratedMethodList
     */
    private $integratedMethodList;

    /**
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param IntegratedMethodList $integratedMethodList
     */
    public function __construct(
        ThirdPartyModuleManager $thirdPartyModuleManager,
        IntegratedMethodList $integratedMethodList
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->integratedMethodList = $integratedMethodList;
    }

    /**
     * Modify results of getPaymentMethods() call to remove not installed methods
     *
     * @param Data $subject
     * @param $result
     * @return array
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterGetPaymentMethods(Data $subject, $result)
    {
        //todo: begin legacy mechanism, need replace to IntegratedMethodPool
        if (!$this->thirdPartyModuleManager->isNmiModuleEnabled()) {
            unset($result['aw_nmi'], $result['aw_sarp_aw_nmi_recurring']);
        }
        
        if (!$this->thirdPartyModuleManager->isBamboraApacModuleEnabled()) {
            unset($result['aw_bambora_apac'], $result['aw_sarp_aw_bambora_apac_recurring']);
        }

        if (!$this->thirdPartyModuleManager->isAuthorizenetAcceptJsModuleEnabled()) {
            unset($result['authorizenet_acceptjs'], $result['aw_sarp_authorizenet_acceptjs_recurring']);
        }
        //todo: end legacy mechanism

        foreach ($this->integratedMethodList->getList() as $integratedMethod) {
            if (!$integratedMethod->isEnablePaymentModule()) {
                unset(
                    $result[$integratedMethod->getCode()],
                    $result[$integratedMethod->getRecurringCode()]
                );
            }
        }

        return $result;
    }
}
