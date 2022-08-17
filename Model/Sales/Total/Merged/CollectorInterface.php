<?php
namespace Aheadworks\Sarp2\Model\Sales\Total\Merged;

/**
 * Interface CollectorInterface
 * @package Aheadworks\Sarp2\Model\Sales\Total\Merged
 */
interface CollectorInterface
{
    /**
     * Collect merged profile totals
     *
     * @param Subject $subject
     * @return $this
     */
    public function collect(Subject $subject);
}
