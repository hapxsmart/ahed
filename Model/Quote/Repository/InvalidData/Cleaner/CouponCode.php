<?php
namespace Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner;

use Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\CleanerInterface;

/**
 * Class CouponCode
 * @package Aheadworks\Sarp2\Model\Quote\Repository\InvalidData\Cleaner
 */
class CouponCode implements CleanerInterface
{
    /**
     * {@inheritdoc}
     */
    public function clean($quote)
    {
        $quote->setCouponCode('');
        return $quote;
    }
}
