<?php
namespace Aheadworks\Sarp2\Model\Email\Sender;

use Aheadworks\Sarp2\Engine\NotificationInterface;

/**
 * Interface EnablerInterface
 * @package Aheadworks\Sarp2\Model\Email\Sender
 */
interface EnablerInterface
{
    /**
     * Check if sender enabled
     *
     * @param NotificationInterface $notification
     * @return bool
     */
    public function isEnabled($notification);
}
