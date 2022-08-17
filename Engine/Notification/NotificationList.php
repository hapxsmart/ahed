<?php
namespace Aheadworks\Sarp2\Engine\Notification;

use Aheadworks\Sarp2\Engine\Notification;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification\CollectionFactory;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class NotificationList
 * @package Aheadworks\Sarp2\Engine\Notification
 */
class NotificationList
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param CollectionFactory $collectionFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * Get planned notifications for order
     *
     * @param int $orderId
     * @param string $type
     * @return NotificationInterface[]
     */
    public function getPlannedForOrder($orderId, $type)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('type', ['eq' => $type])
            ->addFieldToFilter('order_id', ['eq' => $orderId])
            ->addFieldToFilter('status', ['eq' => NotificationInterface::STATUS_PLANNED]);
        return $collection->getItems();
    }

    /**
     * Get ready for send notifications for today
     *
     * @return NotificationInterface[]
     */
    public function getReadyForSendNotificationsForToday()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => NotificationInterface::STATUS_READY])
            ->addFieldToFilter(
                'scheduled_at',
                ['lteq' => $this->dateTime->formatDate(true, true)]
            );
        return $collection->getItems();
    }

    /**
     * Get ready for send notifications for profile
     *
     * @param int $profileId
     * @return NotificationInterface[]|Notification[]
     */
    public function getReadyForSendNotificationsForProfile($profileId)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection
            ->addFieldToFilter('status', ['eq' => NotificationInterface::STATUS_READY])
            ->addFieldToFilter('profile_id', ['eq' => $profileId]);
        return $collection->getItems();
    }
}
