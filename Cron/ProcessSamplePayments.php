<?php
namespace Aheadworks\Sarp2\Cron;

use Aheadworks\Sarp2\Model\Payment\SamplerManagement;

/**
 * Class ProcessSamplePayments
 *
 * @package Aheadworks\Sarp2\Cron
 */
class ProcessSamplePayments
{
    /**
     * @var SamplerManagement
     */
    private $samplerManagement;

    /**
     * @param SamplerManagement $samplerManagement
     */
    public function __construct(
        SamplerManagement $samplerManagement
    ) {
        $this->samplerManagement = $samplerManagement;
    }

    /**
     * Perform processing of placed sample payments
     *
     * @return void
     */
    public function execute()
    {
        $this->samplerManagement->revertPayments();
    }
}
