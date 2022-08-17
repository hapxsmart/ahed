<?php
namespace Aheadworks\Sarp2\Model\Profile\Data\Operation;

use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Api\ProfileManagementInterface;
use Aheadworks\Sarp2\Model\Profile\Data\OperationInterface;
use Magento\Framework\Exception\LocalizedException;

class RemoveItem implements OperationInterface
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
     * Remove profile item
     *
     * @param int $profileId
     * @param array $data
     * @throws LocalizedException
     */
    public function execute(int $profileId, array $data)
    {
        $this->profileManagement->removeItem($profileId, $data[ProfileItemInterface::ITEM_ID]);
    }
}
