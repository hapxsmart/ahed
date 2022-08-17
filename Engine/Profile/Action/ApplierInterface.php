<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultInterface;

/**
 * Interface ApplierInterface
 * @package Aheadworks\Sarp2\Engine\Profile\Action
 */
interface ApplierInterface
{
    /**
     * Apply action
     *
     * @param ProfileInterface $profile
     * @param ActionInterface $action
     * @return ProfileInterface
     */
    public function apply(ProfileInterface $profile, ActionInterface $action);

    /**
     * Validate action to be applied on specified profile
     *
     * @param ProfileInterface $profile
     * @param ActionInterface $action
     * @return ResultInterface
     */
    public function validate(ProfileInterface $profile, ActionInterface $action);
}
