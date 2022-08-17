<?php
namespace Aheadworks\Sarp2\Engine\Notification\Checker;

use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class IsSendable
 * @package Aheadworks\Sarp2\Engine\Notification\Checker
 */
class IsSendable
{
    /**
     * @var array
     */
    private $typeToProfileStatusesRestrictedMap = [
        NotificationInterface::TYPE_UPCOMING_BILLING => [
            Status::CANCELLED,
            Status::EXPIRED,
            Status::SUSPENDED
        ]
    ];

    /**
     * Check if notification is sendable
     *
     * @param NotificationInterface $notification
     * @return bool
     */
    public function check(NotificationInterface $notification)
    {
        $notificationType = $notification->getType();
        return isset($this->typeToProfileStatusesRestrictedMap[$notificationType])
            ? !in_array(
                $notification->getProfileStatus(),
                $this->typeToProfileStatusesRestrictedMap[$notificationType]
            )
            : true;
    }
}
