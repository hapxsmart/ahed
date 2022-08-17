<?php
namespace Aheadworks\Sarp2\Observer;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AssignProfileToCustomerObserver
 * @package Aheadworks\Sarp2\Observer
 */
class AssignProfileToCustomerObserver implements ObserverInterface
{
    /**
     * Order id key
     */
    const SALES_ASSIGN_ORDER_ID_KEY = '__sales_assign_order_id';

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->profileRepository = $profileRepository;
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var CustomerInterface $customer */
        $customer = $event->getData('customer_data_object');
        /** @var array $delegateData */
        $delegateData = $event->getData('delegate_data');
        if ($delegateData && array_key_exists(self::SALES_ASSIGN_ORDER_ID_KEY, $delegateData)) {
            $orderId = $delegateData[self::SALES_ASSIGN_ORDER_ID_KEY];
            $this->searchCriteriaBuilder
                ->addFilter(ProfileOrderInterface::ORDER_ID, $orderId, 'eq');
            /** @var ProfileOrderInterface[] $profileOrders */
            $profileOrders = $this->profileOrderRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();

            if (count($profileOrders) > 0) {
                /** @var ProfileOrderInterface $profileOrder */
                foreach ($profileOrders as $profileOrder) {
                    try {
                        /** @var ProfileInterface $profile */
                        $profile = $this->profileRepository->get($profileOrder->getProfileId());
                        if (!$profile->getCustomerId()) {
                            $profile
                                ->setCustomerId($customer->getId())
                                ->setCustomerIsGuest(false)
                                ->setCustomerWasGuest(true);
                            $this->profileRepository->save($profile);
                        }
                    } catch (NoSuchEntityException $e) {
                    }
                }
            }
        }
    }
}
