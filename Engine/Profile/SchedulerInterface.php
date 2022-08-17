<?php
namespace Aheadworks\Sarp2\Engine\Profile;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;

/**
 * Class SaveHandler
 * @package Aheadworks\Sarp2\Engine\Profile
 */
interface SchedulerInterface
{
    /**
     * Schedule profile
     *
     * @param ProfileInterface[] $profiles
     * @return ProfileInterface[]
     * @throws \Aheadworks\Sarp2\Engine\Exception\CouldNotScheduleException
     */
    public function schedule($profiles);
}
