<?php
namespace Aheadworks\Sarp2\Engine\Profile\Item\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Model\Profile\Item\Checker\IsOneOffItem;
use Aheadworks\Sarp2\Model\Profile\View\Action\Permission as ActionPermission;
use Magento\Framework\Exception\LocalizedException;

class IsRemoveActionAvailable
{
    /**
     * @var IsOneOffItem
     */
    private $isOneOffItem;

    /**
     * @var ActionPermission
     */
    private $actionPermission;

    /**
     * @param ActionPermission $actionPermission
     * @param IsOneOffItem $isOneOffItem
     */
    public function __construct(
        ActionPermission $actionPermission,
        IsOneOffItem $isOneOffItem
    ) {
        $this->actionPermission = $actionPermission;
        $this->isOneOffItem = $isOneOffItem;
    }

    /**
     * Check if remove action available
     *
     * @param ProfileInterface $profile
     * @param ProfileItemInterface $profileItem
     * @return bool
     * @throws LocalizedException
     */
    public function check(ProfileInterface $profile, ProfileItemInterface $profileItem): bool
    {
        $itemsCount = 0;
        $subscriptionItems = 0;
        $oneOffItems = 0;

        foreach ($profile->getItems() as $item) {
            if (!$item->getParentItem()) {
                $itemsCount++;
            }
            $this->isOneOffItem->check($item) ? $oneOffItems++ : $subscriptionItems++;
        }

        return $this->isOneOffItem->check($profileItem)
            || $itemsCount - $oneOffItems > 1
            && $this->actionPermission->isCancelStatusAvailable($profile->getProfileId());
    }
}
