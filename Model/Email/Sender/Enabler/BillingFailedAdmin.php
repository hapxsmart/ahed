<?php
namespace Aheadworks\Sarp2\Model\Email\Sender\Enabler;

use Aheadworks\Sarp2\Model\Email\Sender\EnablerInterface;

/**
 * Class BillingFailedAdmin
 *
 * @package Aheadworks\Sarp2\Model\Email\Sender\Enabler
 */
class BillingFailedAdmin implements EnablerInterface
{
    /**
     * {@inheritdoc}
     */
    public function isEnabled($notification)
    {
        return true;
    }
}
