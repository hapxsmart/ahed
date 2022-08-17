<?php
namespace Aheadworks\Sarp2\Model\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\Data\ProfileItemInterface;

/**
 * Class Registry
 *
 * @package Aheadworks\Sarp2\Model\Profile
 */
class Registry
{
    /**
     * @var ProfileInterface
     */
    private $profile;

    /**
     * @var ProfileItemInterface
     */
    private $profileItem;

    /**
     * Add profile to registry
     *
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile(ProfileInterface $profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Add profile item to registry
     *
     * @param ProfileItemInterface $item
     * @return $this
     */
    public function setProfileItem(ProfileItemInterface $item)
    {
        $this->profileItem = $item;

        return $this;
    }

    /**
     * Retrieve profile from registry
     *
     * @return ProfileInterface
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Retrieve profile item from registry
     *
     * @return ProfileItemInterface
     */
    public function getProfileItem()
    {
        return $this->profileItem;
    }
}
