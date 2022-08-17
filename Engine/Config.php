<?php
namespace Aheadworks\Sarp2\Engine;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 * @package Aheadworks\Sarp2\Engine
 */
class Config
{
    /**
     * Configuration path to use bundled payments flag
     */
    const XML_PATH_USE_BUNDLED_PAYMENTS = 'aw_sarp2/engine/use_bundled_payments';

    /**
     * Configuration path to use bundled virtual profiles flag
     */
    const XML_PATH_BUNDLE_VIRTUAL = 'aw_sarp2/engine/bundle_virtual';

    /**
     * Configuration path to bundled payments failure handling type
     */
    const XML_PATH_BUNDLE_FAILURE_HANDLING = 'aw_sarp2/engine/bundled_failure_handling';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if bundled payments enabled
     *
     * @return bool
     */
    public function isBundledPaymentsEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_USE_BUNDLED_PAYMENTS);
    }

    /**
     * Check if virtual profiles should be bundled into single payment
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isVirtualProfilesBundleEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_BUNDLE_VIRTUAL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get bundled payments failure handling type
     *
     * @param int|null $storeId
     * @return string
     */
    public function getBundledFailureHandlingType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BUNDLE_FAILURE_HANDLING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
