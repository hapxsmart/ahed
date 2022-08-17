<?php
namespace Aheadworks\Sarp2\Cron;

use Aheadworks\Sarp2\Engine\Notification\NotifierInterface;

/**
 * Class ProcessNotifications
 * @package Aheadworks\Sarp2\Cron
 */
class ProcessNotifications
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @param NotifierInterface $notifier
     */
    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * Perform processing of notifications
     *
     * @return void
     */
    public function execute()
    {
        $this->notifier->processNotificationsForToday();
    }
}
