<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Validation\Validator;

use Aheadworks\Sarp2\Api\PlanRepositoryInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class IsPlanAvailable
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Validation\Validator
 */
class IsPlanAvailable extends AbstractValidator
{
    /**
     * @var PlanRepositoryInterface
     */
    private $planRepository;

    /**
     * @var State
     */
    private $state;

    /**
     * @param PlanRepositoryInterface $planRepository
     * @param State $state
     */
    public function __construct(
        PlanRepositoryInterface $planRepository,
        State $state
    ) {
        $this->planRepository = $planRepository;
        $this->state = $state;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function performValidation($profile, $action)
    {
        try {
            $this->planRepository->get($profile->getPlanId());
        } catch (LocalizedException $exception) {
            $message = $this->state->getAreaCode() == Area::AREA_FRONTEND
                ? __('We are sorry, the plan %1 is not available anymore.'
                    . ' Please select another plan first.', $profile->getPlanName())
                : __('It looks like the plan %1 has been deleted.'
                    . ' Please select another plan first.', $profile->getPlanName());
            $this->addMessages([$message]);
        }
    }
}
