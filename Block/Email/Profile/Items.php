<?php
declare(strict_types=1);

namespace Aheadworks\Sarp2\Block\Email\Profile;

use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;
use Aheadworks\Sarp2\Block\Email\Items\AbstractItems;
use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class Items
 *
 * @method \Aheadworks\Sarp2\ViewModel\Profile getProfileViewModel()
 */
class Items extends AbstractItems
{
    /**
     * @inheritdoc
     */
    protected $_template = 'email/profile/items.phtml';

    /**
     * @inheritdoc
     */
    protected function getItemType($item) : string
    {
        /** @var ProfileItemInterface $item */
        return $item->getProductType();
    }

    /**
     * Get profile
     *
     * @return ProfileInterface
     * @throws LocalizedException
     */
    public function getProfile() : ProfileInterface
    {
        $profile = $this->getData('profile');
        return $profile ?? $this->getProfileViewModel()->getProfile((int)$this->getData('profile_id'));
    }
}
