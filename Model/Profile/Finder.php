<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileSearchResultsInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Finder
 * @package Aheadworks\Sarp2\Model\Customer
 */
class Finder
{
    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @param ProfileRepositoryInterface $profileRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     */
    public function __construct(
        ProfileRepositoryInterface $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProfileOrderRepositoryInterface $profileOrderRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->profileOrderRepository = $profileOrderRepository;
    }

    /**
     * Get active profiles
     *
     * @param int $customerId
     * @return ProfileInterface[]
     * @throws LocalizedException
     */
    public function getActiveProfilesByCustomerId($customerId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileInterface::CUSTOMER_ID, $customerId, 'eq')
            ->addFilter(ProfileInterface::STATUS, Status::ACTIVE, 'eq');

        /** @var ProfileSearchResultsInterface $searchResult */
        $searchResults = $this->profileRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getItems();
    }

    /**
     * Get actual profiles by plan id
     *
     * @param int $planId
     * @return ProfileInterface[]
     * @throws LocalizedException
     */
    public function getActualProfilesByPlanId($planId)
    {
        $this->searchCriteriaBuilder
            ->addFilter("main_table.".ProfileInterface::PLAN_ID, $planId, 'eq')
            ->addFilter("main_table.".ProfileInterface::STATUS,
                [Status::ACTIVE, Status::SUSPENDED, Status::PENDING],
                'in'
            );

        /** @var ProfileSearchResultsInterface $searchResult */
        $searchResults = $this->profileRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getItems();
    }

    /**
     * Find subscription profile by order id
     *
     * @param int $orderId
     * @param int $planId
     * @return ProfileInterface|null
     */
    public function getByOrderAndPlan($orderId, $planId)
    {
        $profiles = $this->getByOrder($orderId);
        foreach ($profiles as $profile) {
            if ($profile->getPlanId() == $planId) {
                return $profile;
            }
        }

        return null;
    }

    /**
     * Find subscription profiles by order id
     *
     * @param int $orderId
     * @return ProfileInterface[]
     */
    public function getByOrder($orderId)
    {
        $profiles = [];

        $profileOrders = $this->getProfileOrders($orderId);
        if (count($profileOrders) > 0) {
            /** @var ProfileOrderInterface $profileOrder */
            foreach ($profileOrders as $profileOrder) {
                try {
                    /** @var ProfileInterface $profile */
                    $profile = $this->profileRepository->get($profileOrder->getProfileId());
                    $profiles[] = $profile;
                } catch (\Exception $exception) {
                }
            }
        }

        return $profiles;
    }

    /**
     * Find subscription profiles by payment token id
     *
     * @param int $tokenId
     * @return ProfileInterface[]
     * @throws LocalizedException
     */
    public function getByTokenId($tokenId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileInterface::PAYMENT_TOKEN_ID, $tokenId, 'eq');

        /** @var ProfileSearchResultsInterface $searchResult */
        $searchResults = $this->profileRepository->getList($this->searchCriteriaBuilder->create());

        return $searchResults->getItems();
    }

    /**
     * Find profile orders by sales order id
     *
     * @param int $orderId
     * @return ProfileOrderInterface[]
     */
    private function getProfileOrders($orderId)
    {
        try {
            $this->searchCriteriaBuilder
                ->addFilter(ProfileOrderInterface::ORDER_ID, $orderId, 'eq');
            $profileOrders = $this->profileOrderRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
        } catch (LocalizedException $exception) {
            $profileOrders = [];
        }

        return $profileOrders;
    }
}
