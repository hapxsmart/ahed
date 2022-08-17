<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;

/**
 * Interface DetectorInterface
 * @package Aheadworks\Sarp2\Engine\Profile\Action
 */
interface DetectorInterface
{
    /**
     * Detect possible action on profile data change
     *
     * @param ProfileInterface $profile
     * @return ActionInterface|null
     */
    public function detect(ProfileInterface $profile);
}
