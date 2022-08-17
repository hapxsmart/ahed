<?php
namespace Aheadworks\Sarp2\Engine;

use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence as SchedulePersistence;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment as PaymentResource;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\CollectionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Payment extends AbstractModel implements PaymentInterface
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var SchedulePersistence
     */
    private $schedulePersistence;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'item_id';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ProfileRepositoryInterface $profileRepository
     * @param SchedulePersistence $schedulePersistence
     * @param PaymentFactory $paymentFactory
     * @param CollectionFactory $collectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProfileRepositoryInterface $profileRepository,
        SchedulePersistence $schedulePersistence,
        PaymentFactory $paymentFactory,
        CollectionFactory $collectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->profileRepository = $profileRepository;
        $this->schedulePersistence = $schedulePersistence;
        $this->paymentFactory = $paymentFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(PaymentResource::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->getData('parent_item_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($parentId)
    {
        return $this->setData('parent_item_id', $parentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getParentItem()
    {
        $parentItemId = $this->getParentId();
        if ($this->getData('parent_item') === null && $parentItemId) {
            /** @var Payment $parentItem */
            $parentItem = $this->paymentFactory->create();
            $this->_resource->load($parentItem);
            $this->setData('parent_item', $parentItem);
        }
        return $this->getData('parent_item');
    }

    /**
     * {@inheritdoc}
     */
    public function setParentItem($parentItem)
    {
        return $this->setData('parent_item', $parentItem);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildItems()
    {
        $paymentId = $this->getId();
        if ($this->getData('child_items') === null && $paymentId) {
            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('parent_item_id', ['eq' => $paymentId]);
            $this->setData('child_items', $collection->getItems());
        }
        return $this->getData('child_items');
    }

    /**
     * {@inheritdoc}
     */
    public function isBundled()
    {
        $childItems = $this->getChildItems();
        return $childItems && count($childItems) > 0;
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
    public function getProfile($forceLoad = false)
    {
        $profileId = $this->getProfileId();
        if ((!$this->getData('profile') || $forceLoad) && $profileId) {
            $this->setData('profile', $this->profileRepository->get($profileId));
        }
        return $this->getData('profile');
    }

    /**
     * {@inheritdoc}
     */
    public function setProfile($profile)
    {
        return $this->setData('profile', $profile);
    }

    /**
     * Get payment store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * Set payment store Id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
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
    public function getPaymentPeriod()
    {
        return $this->getData('payment_period');
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentPeriod($paymentPeriod)
    {
        return $this->setData('payment_period', $paymentPeriod);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentStatus()
    {
        return $this->getData('payment_status');
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentStatus($paymentStatus)
    {
        return $this->setData('payment_status', $paymentStatus);
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
    public function getPaidAt()
    {
        return $this->getData('paid_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setPaidAt($paidAt)
    {
        return $this->setData('paid_at', $paidAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getRetryAt()
    {
        return $this->getData('retry_at');
    }

    /**
     * {@inheritdoc}
     */
    public function setRetryAt($retryAt)
    {
        return $this->setData('retry_at', $retryAt);
    }

    /**
     * {@inheritdoc}
     */
    public function getRetriesCount()
    {
        return $this->getData('retries_count');
    }

    /**
     * {@inheritdoc}
     */
    public function setRetriesCount($retriesCount)
    {
        return $this->setData('retries_count', $retriesCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalScheduled()
    {
        return $this->getData('total_scheduled');
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalScheduled($totalScheduled)
    {
        return $this->setData('total_scheduled', $totalScheduled);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTotalScheduled()
    {
        return $this->getData('base_total_scheduled');
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTotalScheduled($baseTotalScheduled)
    {
        return $this->setData('base_total_scheduled', $baseTotalScheduled);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalPaid()
    {
        return $this->getData('total_paid');
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalPaid($totalPaid)
    {
        return $this->setData('total_paid', $totalPaid);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseTotalPaid()
    {
        return $this->getData('base_total_paid');
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTotalPaid($baseTotalPaid)
    {
        return $this->setData('base_total_paid', $baseTotalPaid);
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

    /**
     * {@inheritdoc}
     */
    public function getPaymentData()
    {
        return $this->getData('payment_data');
    }

    /**
     * {@inheritdoc}
     */
    public function setPaymentData($paymentData)
    {
        return $this->setData('payment_data', $paymentData);
    }

    /**
     * {@inheritdoc}
     */
    public function getScheduleId()
    {
        return $this->getData('schedule_id');
    }

    /**
     * {@inheritdoc}
     */
    public function setScheduleId($scheduleId)
    {
        return $this->setData('schedule_id', $scheduleId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedule()
    {
        $scheduleId = $this->getScheduleId();
        if ($this->getData('schedule') === null && $scheduleId) {
            $this->setData('schedule', $this->schedulePersistence->get($scheduleId));
        }
        return $this->getData('schedule');
    }

    /**
     * {@inheritdoc}
     */
    public function setSchedule($schedule)
    {
        return $this->setData('schedule', $schedule);
    }
}
