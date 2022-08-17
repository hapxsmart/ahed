<?php
namespace Aheadworks\Sarp2\Model\Profile\Data\Operation;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface;
use Magento\Framework\Exception\LocalizedException;

class ChangeItem implements OperationInterface
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
     * Change subscription item
     *
     * @param int $profileId
     * @param array $data
     * @return void
     * @throws LocalizedException
     */
    public function execute(int $profileId, array $data)
    {
        $isOneTimeOnly = $data['is_one_time_only'];
        $itemId = $data[ProfileItemInterface::ITEM_ID];
        $buyRequest = $data['product_options']['info_buyRequest'];

        $this->profileManagement->changeProductItem($profileId, $itemId, $buyRequest, $isOneTimeOnly);
    }
}