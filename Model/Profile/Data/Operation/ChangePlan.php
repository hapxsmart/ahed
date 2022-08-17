<?php
namespace Aheadworks\Sarp2\Model\Profile\Data\Operation;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface;
use Magento\Framework\Exception\LocalizedException;

class ChangePlan implements OperationInterface
{
    /**
     * @var ProfileManagementInterface
     */
    private $profileManagement;

    /**
     * @param ProfileManagementInterface $profileManagement
     */
    public function __construct(
        ProfileManagementInterface $profileManagement
    ) {
        $this->profileManagement = $profileManagement;
    }

    /**
     * Change subscription plan
     *
     * @param int $profileId
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $profileId, array $data)
    {
        $planId = $data[ProfileInterface::PLAN_ID] ?? null;
        $this->profileManagement->changeSubscriptionPlan($profileId, $planId);
    }
}