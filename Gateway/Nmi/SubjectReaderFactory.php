<?php
namespace Aheadworks\Sarp2\Gateway\Nmi;

use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class SubjectReaderFactory
 *
 * @package Aheadworks\Sarp2\Gateway\Nmi
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
     * @return \Aheadworks\Nmi\Gateway\SubjectReader|null
     */
    public function getInstance()
    {
        if ($this->thirdPartyModuleManager->isNmiModuleEnabled()) {
            $instance = $this->objectManager->get(\Aheadworks\Nmi\Gateway\SubjectReader::class);
        } else {
            $instance = null;
        }

        return $instance;
    }
}
