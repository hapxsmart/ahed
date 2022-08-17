<?php
namespace Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs;

use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubjectReaderFactory
 *
 * @package Aheadworks\Sarp2\Gateway\AuthorizenetAcceptjs
 */
class SubjectReaderFactory
{
    /**
     * @var ThirdPartyModuleManager
     */
    private $thirdPartyModuleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ThirdPartyModuleManager $thirdPartyModuleManager
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ThirdPartyModuleManager $thirdPartyModuleManager,
        ObjectManagerInterface $objectManager
    ) {
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Get subjectReader object instance
     *
     * @return \Magento\AuthorizenetAcceptjs\Gateway\SubjectReader|null
     */
    public function getInstance()
    {
        if ($this->thirdPartyModuleManager->isAuthorizenetAcceptJsModuleEnabled()) {
            $instance = $this->objectManager->get(\Magento\AuthorizenetAcceptjs\Gateway\SubjectReader::class);
        } else {
            $instance = null;
        }

        return $instance;
    }
}
