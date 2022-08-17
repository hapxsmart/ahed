<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;

/**
 * Interface StatusApplierInterface
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status
 */
interface StatusApplierInterface
{
    /**
     * Apply action
     *
     * @param ProfileInterface $profile
     * @param ActionInterface $action
     * @return ProfileInterface
     */
    public function apply(ProfileInterface $profile, ActionInterface $action);
}
