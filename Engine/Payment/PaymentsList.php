<?php
namespace Aheadworks\Sarp2\Engine\Payment;

use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment\Checker\IsProcessable;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\Collection;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Payment\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime;

class PaymentsList
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var IsProcessable
     */
    private $isProcessableChecker;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param CollectionFactory $collectionFactory
     * @param IsProcessable $isProcessableChecker
     * @param DateTime $dateTime
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        IsProcessable $isProcessableChecker,
        DateTime $dateTime
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->isProcessableChecker = $isProcessableChecker;
        $this->dateTime = $dateTime;
    }

    /**
     * Check if there are payments for specified profile
     *
     * @param int $profileId
     * @return bool
     */
    public function hasForProfile($profileId)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('profile_id', ['eq' => $profileId]);
        return $collection->getSize() > 0;
    }

    /**
     * Get processable payments for today
     *
     * @param string $type
     * @param int $storeId
     * @param int $tmzOffset
     * @param array|null $ids
     * @return Payment[]
     * @throws \Exception
     */
    public function getProcessablePaymentsForToday($type, $storeId, $tmzOffset, $ids = null)
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'type',
            ['eq' => $type]
        )->addFieldToFilter(
            'payment_status',
            ['in' => $this->isProcessableChecker->getAvailablePaymentStatuses($type)]
        )->addFieldToFilter(
            'store_id',
            ['eq' => $storeId]
        )->addFieldToFilter(
            $type == PaymentInterface::TYPE_REATTEMPT
                ? 'retry_at'
                : 'scheduled_at',
            ['lteq' => $this->dateTime->formatDate($this->today($tmzOffset))]
        );

        if ($ids) {
            $collection->addFieldToFilter('item_id', ['in' => $ids]);
        }
        return $collection->getItems();
    }

    /**
     * Get first scheduled payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface[]
     */
    public function getFirstScheduledOrPaid($profileId, $additionalFilters = [])
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [
                PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PLANNED, PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [
                    PaymentInterface::STATUS_PLANNED,
                    PaymentInterface::STATUS_PAID
                ],
                PaymentInterface::TYPE_ACTUAL => [PaymentInterface::STATUS_PENDING, PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_REATTEMPT => [
                    PaymentInterface::STATUS_PENDING,
                    PaymentInterface::STATUS_RETRYING,
                    PaymentInterface::STATUS_PAID
                ],
                PaymentInterface::TYPE_OUTSTANDING => [
                    PaymentInterface::STATUS_OUTSTANDING,
                    PaymentInterface::STATUS_PAID
                ]
            ]
        )->setOrder('scheduled_at', Collection::SORT_ORDER_ASC);

        $this->addFilterArrayToCollection($collection, $additionalFilters);

        return $collection->getItems();
    }

    /**
     * Get last scheduled payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface[]
     */
    public function getLastScheduled($profileId, $additionalFilters = [])
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [
                PaymentInterface::TYPE_PLANNED => [
                    PaymentInterface::STATUS_PLANNED
                ],
                PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [PaymentInterface::STATUS_PLANNED],
                PaymentInterface::TYPE_ACTUAL => [PaymentInterface::STATUS_PENDING],
                PaymentInterface::TYPE_REATTEMPT => [
                    PaymentInterface::STATUS_PENDING,
                    PaymentInterface::STATUS_RETRYING
                ],
                PaymentInterface::TYPE_OUTSTANDING => [PaymentInterface::STATUS_OUTSTANDING]
            ]
        )->setOrder('scheduled_at', Collection::SORT_ORDER_ASC);

        $this->addFilterArrayToCollection($collection, $additionalFilters);

        return $collection->getItems();
    }

    /**
     * Get last cancelled payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface[]
     */
    public function getLastCancelled($profileId, $additionalFilters = [])
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_CANCELLED]]
        )->setOrder('scheduled_at', Collection::SORT_ORDER_ASC);

        $this->addFilterArrayToCollection($collection, $additionalFilters);

        return $collection->getItems();
    }

    /**
     * Get last paid payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface|DataObject
     */
    public function getLastPaid($profileId, $additionalFilters = [])
    {
        $collection = $this->getCollectionPaid($profileId, $additionalFilters);

        return $collection
            ->setOrder('scheduled_at', Collection::SORT_ORDER_DESC)
            ->getFirstItem();
    }

    /**
     * Get first failed payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface|DataObject
     */
    public function getFirstPaid($profileId, $additionalFilters = [])
    {
        $collection = $this->getCollectionPaid($profileId, $additionalFilters);

        return $collection
            ->setOrder('scheduled_at', Collection::SORT_ORDER_ASC)
            ->getFirstItem();

    }

    /**
     * Get first failed payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return Collection
     */
    private function getCollectionPaid($profileId, $additionalFilters = []) {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [
                PaymentInterface::TYPE_PLANNED => [PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_LAST_PERIOD_HOLDER => [PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_ACTUAL => [PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_REATTEMPT => [PaymentInterface::STATUS_PAID],
                PaymentInterface::TYPE_OUTSTANDING => [PaymentInterface::STATUS_PAID]
            ]
        );

        $this->addFilterArrayToCollection($collection, $additionalFilters);

        return $collection;
    }

    /**
     * Get last failed payment of profile
     *
     * @param int $profileId
     * @param DataObject[] $additionalFilters
     * @return PaymentInterface
     */
    public function getLastFailed($profileId, $additionalFilters = [])
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(
            'profile_id',
            ['eq' => $profileId]
        )->addTypeStatusMapFilter(
            [
                PaymentInterface::TYPE_REATTEMPT => [
                    PaymentInterface::STATUS_FAILED
                ],
            ]
        )->setOrder('scheduled_at', Collection::SORT_ORDER_DESC);

        $this->addFilterArrayToCollection($collection, $additionalFilters);

        return $collection->getFirstItem();
    }

    /**
     * Get today date time used in filter condition
     *
     * @param int $offset
     * @return \DateTime
     * @throws \Exception
     */
    private function today($offset)
    {
        $today = new \DateTime();
        if ($offset != 0) {
            $intervalSpec = 'PT' . abs($offset) . 'S';
            if ($offset > 0) {
                $today->sub(new \DateInterval($intervalSpec));
            } else {
                $today->add(new \DateInterval($intervalSpec));
            }
        }
        $today = $this->dateTime->formatDate($today);
        return new \DateTime($today);
    }

    /**
     * Add filters to collection
     *
     * @param Collection $collection
     * @param array $filters
     */
    private function addFilterArrayToCollection($collection, $filters)
    {
        if (!is_array($filters)) {
            $filters = [$filters];
        }

        foreach ($filters as $filter) {
            if ($filter instanceof DataObject) {
                $collection->addFieldToFilter(
                    $filter->getField(),
                    $filter->getCondition()
                );
            }
        }
    }
}
