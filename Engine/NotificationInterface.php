<?php
namespace Aheadworks\Sarp2\Engine;

/**
 * Interface NotificationInterface
 * @package Aheadworks\Sarp2\Engine
 */
interface NotificationInterface
{
    /**
     * Notification types
     */
    const TYPE_BILLING_SUCCESSFUL = 'billing_successful';
    const TYPE_BILLING_FAILED = 'billing_failed';
    const TYPE_BILLING_FAILED_ADMIN = 'billing_failed_admin';
    const TYPE_UPCOMING_BILLING = 'upcoming_billing';
    const TYPE_CANCELLED_SUBSCRIPTION = 'cancelled_subscription';
    const TYPE_CANCELLED_SUBSCRIPTION_ADMIN = 'cancelled_subscription_admin';
    const TYPE_CANCELLED_SUBSCRIPTION_CUSTOMER = 'cancelled_subscription_customer';
    const TYPE_OFFER_EXTEND_SUBSCRIPTION = 'offer_extend_subscription';
    const TYPE_SUBSCRIPTION_CREATED = 'subscription_created';

    /**
     * Notification statuses
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_READY = 'ready';
    const STATUS_SEND = 'send';
    const STATUS_DECLINED = 'declined';

    /**
     * Get notification Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set notification Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get notification type
     *
     * @return string
     */
    public function getType();

    /**
     * Set notification type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get notification status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Set notification status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get scheduled date
     *
     * @return string
     */
    public function getScheduledAt();

    /**
     * Set scheduled date
     *
     * @param string $scheduledAt
     * @return $this
     */
    public function setScheduledAt($scheduledAt);

    /**
     * Get send date
     *
     * @return string
     */
    public function getSendAt();

    /**
     * Set send date
     *
     * @param string $sendAt
     * @return $this
     */
    public function setSendAt($sendAt);

    /**
     * Get notification data
     *
     * @return array
     */
    public function getNotificationData();

    /**
     * Set notification data
     *
     * @param array $notificationData
     * @return $this
     */
    public function setNotificationData($notificationData);

    /**
     * Get store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get profile Id
     *
     * @return int
     */
    public function getProfileId();

    /**
     * Set profile Id
     *
     * @param int $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * Get profile status
     *
     * @return string
     */
    public function getProfileStatus();

    /**
     * Get order Id
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set order Id
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);
}
