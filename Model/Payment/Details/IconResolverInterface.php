<?php
namespace Aheadworks\Sarp2\Model\Payment\Details;

/**
 * Interface IconResolverInterface
 *
 * @package Aheadworks\Sarp2\Model\Payment\Details
 */
interface IconResolverInterface
{
    /**
     * Retrieve icon data array for specific payment type
     *
     * @param string $paymentType
     * @return array
     */
    public function getIconData($paymentType);
}
