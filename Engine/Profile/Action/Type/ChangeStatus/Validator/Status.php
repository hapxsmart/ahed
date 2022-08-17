<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Validator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\StatusMap;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;

/**
 * Class Status
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Validator
 */
class Status extends AbstractValidator
{
    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @var StatusMap
     */
    private $statusMap;

    /**
     * @param StatusMap $statusMap
     * @param StatusSource $statusSource
     */
    public function __construct(
        StatusMap $statusMap,
        StatusSource $statusSource
    ) {
        $this->statusMap = $statusMap;
        $this->statusSource = $statusSource;
    }

    /**
     * @inheritDoc
     */
    protected function performValidation($profile, $action)
    {
        $newStatus = $action->getData()->getStatus();
        $currentStatus = $this->getProfileStatus($profile);
        $statusLabels = $this->statusSource->getOptions();
        $allowedStatuses = $this->statusMap->getAllowedStatuses($currentStatus);

        if (!$newStatus || !in_array($newStatus, $this->statusMap->getAllStatuses())) {
            $this->addMessages([__('Unable to perform action, this status in not valid.')]);
        }

        if ($newStatus == $currentStatus && $currentStatus !== StatusSource::ACTIVE) {
            $this->addMessages([
                __('Unable to perform action, subscription already has "%1" status.', $statusLabels[$newStatus])
            ]);
        }

        if (!in_array($newStatus, $allowedStatuses)) {
            $this->addMessages([__('Profile status %1 is not allowed.', $statusLabels[$newStatus])]);
        }
    }

    /**
     * Get profile status
     *
     * @param ProfileInterface|Profile $profile
     * @return string
     */
    private function getProfileStatus($profile)
    {
        return $profile->getOrigData('status') != $profile->getStatus()
            ? $profile->getOrigData('status')
            : $profile->getStatus();
    }
}
