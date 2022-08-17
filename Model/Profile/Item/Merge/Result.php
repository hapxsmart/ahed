<?php
namespace Aheadworks\Sarp2\Model\Profile\Item\Merge;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

/**
 * Class Result
 * @package Aheadworks\Sarp2\Model\Profile\Item\Merge
 */
class Result
{
    /**
     * @var ProfileItemInterface
     */
    private $item;

    /**
     * @var string
     */
    private $paymentPeriod;

    /**
     * @param ProfileItemInterface $item
     * @param string $paymentPeriod
     */
    public function __construct(
        $item,
        $paymentPeriod
    ) {
        $this->item = $item;
        $this->paymentPeriod = $paymentPeriod;
    }

    /**
     * Get profile item
     *
     * @return ProfileItemInterface
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Get payment period
     *
     * @return string
     */
    public function getPaymentPeriod()
    {
        return $this->paymentPeriod;
    }
}
