<?php
namespace Aheadworks\Sarp2\Gateway\BamboraApac;

use Magento\Framework\ObjectManagerInterface;
use Aheadworks\Sarp2\Model\ThirdPartyModule\Manager as ThirdPartyModuleManager;

/**
 * Class SubjectReaderFactory
 *
 * @package Aheadworks\Sarp2\Gateway\BamboraApac
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
     * @return object|null
     */
    public function getInstance()
    {
        if ($this->thirdPartyModuleManager->isBamboraApacModuleEnabled()) {
            $instance = $this->objectManager->get(\Aheadworks\BamboraApac\Gateway\SubjectReader::class);
        } else {
            $instance = null;
        }

        return $instance;
    }
}
