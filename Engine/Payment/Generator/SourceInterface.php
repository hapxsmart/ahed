<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\PaymentInterface;

/**
 * Interface SourceInterface
 * @package Aheadworks\Sarp2\Engine\Payment\Generator
 */
interface SourceInterface
{
    /**
     * Get profile
     *
     * @return ProfileInterface|null
     */
    public function getProfile();

    /**
     * Get payments
     *
     * @return PaymentInterface[]
     */
    public function getPayments();
}
