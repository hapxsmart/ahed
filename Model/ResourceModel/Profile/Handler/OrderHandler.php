<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Handler;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterface;
use Aheadworks\Sarp2\Api\Data\ProfileOrderInterfaceFactory;
use Aheadworks\Sarp2\Model\ResourceModel\Profile\Order;
use Aheadworks\Sarp2\Model\Profile\Order\Checker as OrderChecker;

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
     * @var OrderChecker
     */
    private $orderChecker;

    /**
     * @param ProfileOrderInterfaceFactory $profileOrderFactory
     * @param Order $profileOrderResource
     * @param OrderChecker $orderChecker
     */
    public function __construct(
        ProfileOrderInterfaceFactory $profileOrderFactory,
        Order $profileOrderResource,
        OrderChecker $orderChecker
    ) {
        $this->profileOrderFactory = $profileOrderFactory;
        $this->profileOrderResource = $profileOrderResource;
        $this->orderChecker = $orderChecker;
    }

    /**
     * @inheritdoc
     *
     * @throws AlreadyExistsException
     */
    public function process(ProfileInterface $profile)
    {
        /** @var OrderInterface $order */
        $order = $profile->getOrder();
        if ($order && !$this->orderChecker->isProfileOrderExists($profile->getProfileId(), $order->getEntityId())) {
            /** @var ProfileOrderInterface|AbstractModel $profileOrder */
            $profileOrder = $this->profileOrderFactory->create();
            $profileOrder
                ->setProfileId($profile->getProfileId())
                ->setOrderId($order->getEntityId())
                ->setIsInitial(!$this->orderChecker->isAtLeastOneOrderExists($profile->getProfileId()));

            $this->profileOrderResource->save($profileOrder);
        }
    }
}
