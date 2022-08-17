<?php
namespace Aheadworks\Sarp2\Engine\Profile\Checker;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * interface MergeableInterface
 */
interface MergeableInterface
{
    /**
     * Check if profiles can be merged
     *
     * @param ProfileInterface $profile1
     * @param ProfileInterface $profile2
     * @return bool
     */
    public function check(ProfileInterface $profile1, ProfileInterface $profile2);
}
