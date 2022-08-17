<?php
namespace Aheadworks\Sarp2\Engine\Notification;

/**
 * Interface NotifierInterface
 * @package Aheadworks\Sarp2\Engine\Notification
 */
interface NotifierInterface
{
    /**
     * Process notifications for today
     *
     * @return void
     */
    public function processNotificationsForToday();
}
