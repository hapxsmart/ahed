<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Notification\Locator\Registry;

/**
 * Class Locator
 * @package Aheadworks\Sarp2\Engine\Notification
 */
class Locator
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var NotificationList
     */
    private $notificationList;

    /**
     * @param Registry $registry
     * @param NotificationList $notificationList
     */
    public function __construct(
        Registry $registry,
        NotificationList $notificationList
    ) {
        $this->registry = $registry;
        $this->notificationList = $notificationList;
    }

    /**
     * Get notification instance for order
     *
     * @param string $type
     * @param int|null $orderId
     * @return NotificationInterface
     */
    public function getNotification($type, $orderId = null)
    {
        $registered = $this->registry->get();
        if (!$registered && $orderId) {
            $notifications = $this->notificationList->getPlannedForOrder($orderId, $type);
            if (count($notifications)) {
                return current($notifications);
            }
        }
        return $registered;
    }
}
