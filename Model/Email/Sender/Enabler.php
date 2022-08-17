<?php
namespace Aheadworks\Sarp2\Model\Email\Sender;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Enabler
 * @package Aheadworks\Sarp2\Model\Email\Sender
 */
class Enabler implements EnablerInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $configPath;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $configPath
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $configPath
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPath = $configPath;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled($notification)
    {
        $storeId = $notification->getStoreId();
        return $this->scopeConfig->isSetFlag(
            $this->configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
