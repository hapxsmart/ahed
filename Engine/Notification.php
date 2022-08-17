<?php
namespace Aheadworks\Sarp2\Engine;

use Aheadworks\Sarp2\Model\ResourceModel\Engine\Notification as NotificationResource;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Notification
 * @package Aheadworks\Sarp2\Engine
 */
class Notification extends AbstractModel implements NotificationInterface
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'notification_id';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(NotificationResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData('type');
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData('type', $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * {@inheritdoc}
     */
    public function getEmail()
    {
        return $this->getData('email');
    }

    /**
     * {@inheritdoc}
     */
    public function setEmail($email)
    {
        return $this->setData('email', $email);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getData('name');
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduledAt()
    {
        return $this->getData('scheduled_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setScheduledAt($scheduledAt)
    {
        return $this->setData('scheduled_at', $scheduledAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getSendAt()
    {
        return $this->getData('send_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setSendAt($sendAt)
    {
        return $this->setData('send_at', $sendAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationData()
    {
        return $this->getData('notification_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setNotificationData($notificationData)
    {
        return $this->setData('notification_data', $notificationData);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileId()
    {
        return $this->getData('profile_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setProfileId($profileId)
    {
        return $this->setData('profile_id', $profileId);
    }

    /**
     * {@inheritdoc}
     */
    public function getProfileStatus()
    {
        return $this->getData('profile_status');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderId($orderId)
    {
        return $this->setData('order_id', $orderId);
    }
}
