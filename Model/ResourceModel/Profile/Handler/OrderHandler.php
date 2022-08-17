<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterfaceFactory;
use Aheadworks\Sarp2\Api\Data\ProfileOrderSearchResultsInterface;
use Aheadworks\Sarp2\Api\ProfileOrderRepositoryInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler\HandlerInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class OrderHandler
 * @package Aheadworks\Sarp2\Model\ResourceModel\Profile
 */
class OrderHandler implements HandlerInterface
{
    /**
     * @var ProfileOrderInterfaceFactory
     */
    private $profileOrderFactory;

    /**
     * @var Order
     */
    private $profileOrderResource;

    /**
     * @var ProfileOrderRepositoryInterface
     */
    private $profileOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProfileOrderInterfaceFactory $profileOrderFactory
     * @param Order $profileOrderResource
     * @param ProfileOrderRepositoryInterface $profileOrderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProfileOrderInterfaceFactory $profileOrderFactory,
        Order $profileOrderResource,
        ProfileOrderRepositoryInterface $profileOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->profileOrderFactory = $profileOrderFactory;
        $this->profileOrderResource = $profileOrderResource;
        $this->profileOrderRepository = $profileOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProfileInterface $profile)
    {
        /** @var OrderInterface $order */
        $order = $profile->getOrder();
        if ($order && !$this->isProfileOrderExists($profile->getProfileId(), $order->getEntityId())) {
            /** @var ProfileOrderInterface $profileOrder */
            $profileOrder = $this->profileOrderFactory->create();
            $profileOrder->setProfileId($profile->getProfileId())
                ->setOrderId($order->getEntityId());
            $this->profileOrderResource->save($profileOrder);
        }
    }

    /**
     * Check if profile order exists
     *
     * @param int $profileId
     * @param int $orderId
     * @return bool
     */
    private function isProfileOrderExists($profileId, $orderId)
    {
        $this->searchCriteriaBuilder
            ->addFilter(ProfileOrderInterface::PROFILE_ID, $profileId)
            ->addFilter(ProfileOrderInterface::ORDER_ID, $orderId);

        try {
            /** @var ProfileOrderSearchResultsInterface $searchResults */
            $searchResults = $this->profileOrderRepository->getList(
                $this->searchCriteriaBuilder->create()
            );
        } catch (\Exception $e) {
            return false;
        }

        return ($searchResults->getTotalCount() > 0);
    }
}
