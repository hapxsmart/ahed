<?php
namespace Aheadworks\Sarp2\Model\ThirdPartyModule;

use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Manager
 *
 * @package Aheadworks\Sarp2\Model\ThirdPartyModule
 */
class Manager
{
    /**
     * Aheadworks Nmi module name
     */
    const NMI_MODULE_NAME = 'Aheadworks_Nmi';

    /**
     * Aheadworks BamboraApac module name
     */
    const BAMBORA_MODULE_NAME = 'Aheadworks_BamboraApac';

    /**
     * Authorizenet AcceptJs module name
     */
    const AUTHORIZENET_ACCEPT_JS_MODULE_NAME = 'Magento_AuthorizenetAcceptjs';

    /**
     * Authorizenet Cardinal module name
     */
    const AUTHORIZENET_CARDINAL_MODULE_NAME = 'Magento_AuthorizenetCardinal';

    /**
     * Aheadworks OneStepCheckout module name
     */
    const OSC_MODULE_NAME = 'Aheadworks_OneStepCheckout';

    const KLARNA_MODULE_NAME = 'Klarna_Kp';

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Check if Aheadworks Nmi module enabled
     *
     * @return bool
     */
    public function isNmiModuleEnabled()
    {
        return $this->moduleList->has(self::NMI_MODULE_NAME);
    }

    /**
     * Check if Aheadworks BamboraApac module enabled
     *
     * @return bool
     */
    public function isBamboraApacModuleEnabled()
    {
        return $this->moduleList->has(self::BAMBORA_MODULE_NAME);
    }

    /**
     * Check if Authorizenet AcceptJs module enabled
     *
     * @return bool
     */
    public function isAuthorizenetAcceptJsModuleEnabled()
    {
        return $this->moduleList->has(self::AUTHORIZENET_ACCEPT_JS_MODULE_NAME);
    }

    /**
     * Check if Authorizenet Cardinal module enabled
     *
     * @return bool
     */
    public function isAuthorizenetCardinalModuleEnabled()
    {
        return $this->moduleList->has(self::AUTHORIZENET_CARDINAL_MODULE_NAME);
    }

    /**
     * Check if OneStepCheckout module enabled
     *
     * @return bool
     */
    public function isOneStepCheckoutModuleEnabled()
    {
        return $this->moduleList->has(self::OSC_MODULE_NAME);
    }

    /**
     * Check if Klarna module enabled
     *
     * @return bool
     */
    public function isKlarnaModuleEnabled()
    {
        return $this->moduleList->has(self::KLARNA_MODULE_NAME);
    }
}
