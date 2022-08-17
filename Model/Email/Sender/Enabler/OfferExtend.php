<?php
namespace Aheadworks\Sarp2\Model\Email\Sender\Enabler;

use Aheadworks\Sarp2\Model\Email\Sender\EnablerInterface;

/**
 * Class OfferExtend
 *
 * @package Aheadworks\Sarp2\Model\Email\Sender\Enabler
 */
class OfferExtend implements EnablerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled($notification)
    {
        return true;
    }
}
