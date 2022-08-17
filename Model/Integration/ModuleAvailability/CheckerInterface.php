<?php
namespace Aheadworks\Sarp2\Model\Integration\ModuleAvailability;

use Aheadworks\Sarp2\Model\Integration\IntegratedMethodInterface;

/**
 * Interface CheckerInterface
 *
 * @package Aheadworks\Sarp2\Model\Integration\ModuleAvailability
 */
interface CheckerInterface
{
    /**
     * Check if integrable payment method module available and enable
     *
     * @param IntegratedMethodInterface $integrableMethod
     * @return bool
     */
    public function check(IntegratedMethodInterface $integrableMethod): bool;
}
