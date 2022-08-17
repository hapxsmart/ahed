<?php
namespace Aheadworks\Sarp2\Engine\Profile\PaymentsInfo;

use Aheadworks\Sarp2\Api\Data\ScheduledPaymentInfoInterface;

/**
 * Interface ProviderInterface
 * @package Aheadworks\Sarp2\Engine\Profile\PaymentsInfo
 */
interface ProviderInterface
{
    /**
     * Get scheduled payments info
     *
     * @param $profileId
     * @return ScheduledPaymentInfoInterface
     */
    public function getScheduledPaymentsInfo($profileId);
}
