<?php
namespace Aheadworks\Sarp2\Model\Profile\Order;

use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SearchCriteria;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;

class Checker
{
    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Check if profile order exists
     *
     * @param int $profileId
     * @param int $orderId
     * @return bool
     */
    public function isProfileOrderExists($profileId, $orderId)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder
            ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId)
            ->addFilter(ProfileOrderInterface::ORDER_ID, $orderId);

        return $this->isFound($searchCriteriaBuilder->create());
    }

    /**
     * Check if at least one profile exists
     *
     * @param int $profileId
     * @return bool
     */
    public function isAtLeastOneOrderExists($profileId)
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteriaBuilder
            ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId);

        return $this->isFound($searchCriteriaBuilder->create());
    }

    /**
     * Check if result is found by search criteria
     *
     * @param SearchCriteria $searchCriteria
     * @return bool
     */
    private function isFound($searchCriteria)
    {
        try {
            /** @var ProfileOrderSearchResultsInterface $searchResults */
            $searchResults = $this->profileOrderRepository->getList($searchCriteria);
        } catch (\Exception $e) {
            return false;
        }

        return ($searchResults->getTotalCount() > 0);
    }
}
