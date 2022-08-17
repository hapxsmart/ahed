<?php
namespace Aheadworks\Sarp2\Model\Integration\ModuleAvailability;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodInterface;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class Checker
 *
 * @package Aheadworks\Sarp2\Model\Integration\ModuleAvailability
 */
class Checker implements CheckerInterface
{
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param ModuleListInterface $moduleList
     */
    public function __construct(ModuleListInterface $moduleList)
    {
        $this->moduleList = $moduleList;
    }

    /**
     * @inheritDoc
     */
    public function check(IntegratedMethodInterface $integrableMethod): bool
    {
        return $this->moduleList->has($integrableMethod->getModuleName());
    }
}
