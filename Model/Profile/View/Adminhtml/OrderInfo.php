<?php
namespace Aheadworks\Sarp2\Model\Profile\View\Adminhtml;

use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Model\Profile\View\Adminhtml\OrderInfo\Counter as OrderCounter;

/**
 * Class OrderInfo
 *
 * @package Aheadworks\Sarp2\Model\Profile\View\Adminhtml
 */
class OrderInfo
{
    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var OrderCounter
     */
    private $orderCounter;

    /**
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ProfileRepositoryInterface $profileRepository
     * @param orderCounter $orderCounter
     */
    public function __construct(
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        ProfileRepositoryInterface $profileRepository,
        orderCounter $orderCounter
    ) {
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->profileRepository = $profileRepository;
        $this->orderCounter = $orderCounter;
    }

    /**
     * Get search criteria builder
     *
     * @return SearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder()
    {
        return $this->searchCriteriaBuilder;
    }

    /**
     * Get profile orders
     *
     * @param int $profileId
     * @return ProfileOrderInterface[]
     * @throws LocalizedException
     */
    public function getProfileOrders($profileId)
    {
        if ($profileId) {
            $orderDateOrder = $this->sortOrderBuilder
                ->setField(ProfileOrderInterface::ORDER_DATE)
                ->setDescendingDirection()
                ->create();
            $this->searchCriteriaBuilder->addSortOrder($orderDateOrder);

            $searchResults = $this->profileOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            return $searchResults->getItems();
        }
        return [];
    }

    /**
     * Get total profile orders count
     *
     * @param int $profileId
     * @return int
     * @throws LocalizedException
     */
    public function getTotalProfileOrdersCount($profileId)
    {
        if ($profileId) {
            $this->searchCriteriaBuilder
                ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId);
            $searchResults = $this->profileOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
            return $searchResults->getTotalCount();
        }
        return 0;
    }

    /**
     * Get left orders count
     *
     * @param int $profileId
     * @return bool|int
     */
    public function getOrdersLeftCount($profileId)
    {
        return $this->orderCounter->countLeftOrders($profileId);
    }
}
