<?php
namespace Aheadworks\Sarp2\Engine;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment\ScheduleInterface;

interface PaymentInterface
{
    /**
     * Payment types
     */
    const TYPE_PLANNED = 'planned';
    const TYPE_ACTUAL = 'actual';
    const TYPE_REATTEMPT = 'reattempt';
    const TYPE_OUTSTANDING = 'outstanding';
    const TYPE_LAST_PERIOD_HOLDER = 'last_period_holder';

    /**
     * Payment periods
     */
    const PERIOD_INITIAL = 'initial';
    const PERIOD_TRIAL = 'trial';
    const PERIOD_REGULAR = 'regular';

    /**
     * Payment statuses
     */
    const STATUS_PLANNED = 'planned';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_RETRYING = 'retrying';
    const STATUS_OUTSTANDING = 'outstanding';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_UNPROCESSABLE = 'unprocessable';

    /**
     * Free payment method
     */
    const FREE_PAYMENT_METHOD = 'aw_sarp2_free_payment';

    /**
     * Get payment Id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set payment Id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get parent payment Id
     *
     * @return int|null
     */
    public function getParentId();

    /**
     * Set parent payment Id
     *
     * @param int|null $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get parent item
     *
     * @return PaymentInterface|null
     */
    public function getParentItem();

    /**
     * Set parent item
     *
     * @param PaymentInterface $parentItem
     * @return $this
     */
    public function setParentItem($parentItem);

    /**
     * Get child items
     *
     * @return PaymentInterface[]
     */
    public function getChildItems();

    /**
     * Check if the payment is bundled
     *
     * @return bool
     */
    public function isBundled();

    /**
     * Get profile Id
     *
     * @return int|null
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
     * Get profile
     *
     * @param bool $forceLoad
     * @return ProfileInterface|null
     */
    public function getProfile($forceLoad = false);

    /**
     * Set profile
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile($profile);

    /**
     * Get payment store Id
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set payment store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get payment type
     *
     * @return string
     */
    public function getType();

    /**
     * Set payment type
     *
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get payment period
     *
     * @return string
     */
    public function getPaymentPeriod();

    /**
     * Set payment period
     *
     * @param $paymentPeriod
     * @return $this
     */
    public function setPaymentPeriod($paymentPeriod);

    /**
     * Get payment status
     *
     * @return string
     */
    public function getPaymentStatus();

    /**
     * Set payment status
     *
     * @param string $paymentStatus
     * @return $this
     */
    public function setPaymentStatus($paymentStatus);

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
     * Get payment date
     *
     * @return string|null
     */
    public function getPaidAt();

    /**
     * Set payment date
     *
     * @param string $paidAt
     * @return $this
     */
    public function setPaidAt($paidAt);

    /**
     * Get retry date
     *
     * @return string|null
     */
    public function getRetryAt();

    /**
     * Set retry date
     *
     * @param string $retryAt
     * @return $this
     */
    public function setRetryAt($retryAt);

    /**
     * Get retries count
     *
     * @return int
     */
    public function getRetriesCount();

    /**
     * Set retries count
     *
     * @param int $retriesCount
     * @return $this
     */
    public function setRetriesCount($retriesCount);

    /**
     * Get total scheduled amount in profile currency
     *
     * @return float
     */
    public function getTotalScheduled();

    /**
     * Set total scheduled amount in profile currency
     *
     * @param float $totalScheduled
     * @return $this
     */
    public function setTotalScheduled($totalScheduled);

    /**
     * Get total scheduled amount in base currency
     *
     * @return float
     */
    public function getBaseTotalScheduled();

    /**
     * Set total scheduled amount in base currency
     *
     * @param float $baseTotalScheduled
     * @return $this
     */
    public function setBaseTotalScheduled($baseTotalScheduled);

    /**
     * Get total paid amount in profile currency
     *
     * @return float
     */
    public function getTotalPaid();

    /**
     * Set total paid amount in profile currency
     *
     * @param float $totalPaid
     * @return $this
     */
    public function setTotalPaid($totalPaid);

    /**
     * Get total paid amount in base currency
     *
     * @return float
     */
    public function getBaseTotalPaid();

    /**
     * Set total paid amount in base currency
     *
     * @param float $baseTotalPaid
     * @return $this
     */
    public function setBaseTotalPaid($baseTotalPaid);

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

    /**
     * Get payment data
     *
     * @return array
     */
    public function getPaymentData();

    /**
     * Set payment data
     *
     * @param array $paymentData
     * @return $this
     */
    public function setPaymentData($paymentData);

    /**
     * Get schedule Id
     *
     * @return int|null
     */
    public function getScheduleId();

    /**
     * Set schedule Id
     *
     * @param int $scheduleId
     * @return $this
     */
    public function setScheduleId($scheduleId);

    /**
     * Get payment schedule
     *
     * @return ScheduleInterface
     */
    public function getSchedule();

    /**
     * Set payment schedule
     *
     * @param ScheduleInterface $schedule
     * @return $this
     */
    public function setSchedule($schedule);
}
