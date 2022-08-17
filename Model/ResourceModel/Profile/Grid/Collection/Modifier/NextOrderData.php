<?php
namespace Aheadworks\Sarp2\Model\ResourceModel\Profile\Grid\Collection\Modifier;

use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Collection\ModifierInterface;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile\ScheduledPaymentInfo\Checker
    as ProfileScheduledPaymentInfoChecker;
use Psr\Log\LoggerInterface;

class NextOrderData implements ModifierInterface
{
    /**#@+
     * Constants defined for additional keys of the item data
     */
    const NEXT_ORDER_GRAND_TOTAL = 'next_order_grand_total';
    const NEXT_ORDER_DATE = 'next_order_date';
    /**#@-*/

    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @var ProfileScheduledPaymentInfoChecker
     */
    private $profileScheduledPaymentInfoChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProfileManagementInterface $profileManagement
     * @param ProfileScheduledPaymentInfoChecker $profileScheduledPaymentInfoChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProfileManagementInterface $profileManagement,
        ProfileScheduledPaymentInfoChecker $profileScheduledPaymentInfoChecker,
        LoggerInterface $logger
    ) {
        $this->profileManagement = $profileManagement;
        $this->profileScheduledPaymentInfoChecker = $profileScheduledPaymentInfoChecker;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function modifyData($item)
    {
        $isNextOrderDataSet = false;
        try {
            /** @var ProfileInterface $item */
            $nextPaymentInfo = $this->profileManagement->getNextPaymentInfo($item->getProfileId());
            if ($this->profileScheduledPaymentInfoChecker->hasScheduledPayment($nextPaymentInfo)) {
                $item->setData(
                    self::NEXT_ORDER_GRAND_TOTAL,
                    $nextPaymentInfo->getBaseAmount()
                );
                $item->setData(
                    self::NEXT_ORDER_DATE,
                    $nextPaymentInfo->getPaymentDate()
                );
                $isNextOrderDataSet = true;
            }

        } catch (\Exception $exception) {
            $this->logger->warning($exception->getMessage());
        }

        if (!$isNextOrderDataSet) {
            $item->setData(
                self::NEXT_ORDER_GRAND_TOTAL,
                null
            );
            $item->setData(
                self::NEXT_ORDER_DATE,
                null
            );
        }

        return $item;
    }
}
