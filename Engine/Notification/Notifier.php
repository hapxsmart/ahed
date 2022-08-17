<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Checker\IsSendable;
use Aheadworks\Sarp2\Model\Email\SenderPool;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Notifier
 * @package Aheadworks\Sarp2\Engine\Notification
 */
class Notifier implements NotifierInterface
{
    /**
     * @var NotificationList
     */
    private $notificationList;

    /**
     * @var SenderPool
     */
    private $senderPool;

    /**
     * @var IsSendable
     */
    private $isSendableChecker;

    /**
     * @var Persistence
     */
    private $persistence;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @param NotificationList $notificationList
     * @param SenderPool $senderPool
     * @param IsSendable $isSendableChecker
     * @param Persistence $persistence
     * @param DateTime $dateTime
     * @param Cleaner $cleaner
     */
    public function __construct(
        NotificationList $notificationList,
        SenderPool $senderPool,
        IsSendable $isSendableChecker,
        Persistence $persistence,
        DateTime $dateTime,
        Cleaner $cleaner
    ) {
        $this->notificationList = $notificationList;
        $this->senderPool = $senderPool;
        $this->isSendableChecker = $isSendableChecker;
        $this->persistence = $persistence;
        $this->dateTime = $dateTime;
        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     */
    public function processNotificationsForToday()
    {
        $notifications = $this->notificationList->getReadyForSendNotificationsForToday();
        /** @var Notification $notification */
        foreach ($notifications as $notification) {
            if ($this->isSendableChecker->check($notification)) {
                try {
                    $sender = $this->senderPool->getSender($notification->getType());
                    $sendResult = $sender->sendIfEnabled($notification);
                    if ($sendResult->isSuccessful()) {
                        $notification->setStatus(NotificationInterface::STATUS_SEND)
                            ->setSendAt($this->dateTime->formatDate(true));
                        $this->persistence->save($notification);
                    } elseif ($sendResult->isDisabled()) {
                        $this->persistence->delete($notification);
                    }
                } catch (\Exception $e) {
                    $this->declineNotification($notification);
                }
            } else {
                $this->declineNotification($notification);
            }
        }

        $this->cleaner->cleanUp();
    }

    /**
     * Decline notification
     *
     * @param Notification $notification
     * @return void
     */
    private function declineNotification($notification)
    {
        $notification->setStatus(NotificationInterface::STATUS_DECLINED);
        $this->persistence->save($notification);
    }
}
