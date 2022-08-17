<?php
namespace Aheadworks\Sarp2\Model\Email;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Model\Email\Send\ResultInterface;

/**
 * Class Sender
 * @package Aheadworks\Sarp2\Model\Email
 */
interface SenderInterface
{
    /**
     * Send email notification if enabled
     *
     * @param NotificationInterface $notification
     * @return ResultInterface
     */
    public function sendIfEnabled(NotificationInterface $notification);
}
