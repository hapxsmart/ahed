<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator;

use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence as SchedulePersistence;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Magento\Framework\Exception\NoSuchEntityException;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class StatusValidator
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend\Validator
 */
class IsOnePeriodBeforeEnd extends AbstractValidator
{
    /**
     * @var SchedulePersistence
     */
    private $schedulePersistence;

    /**
     * @var Checker
     */
    private $scheduleChecker;

    /**
     * @param SchedulePersistence $schedulePersistence
     * @param Checker $scheduleChecker
     */
    public function __construct(
        SchedulePersistence $schedulePersistence,
        Checker $scheduleChecker
    ) {
        $this->schedulePersistence = $schedulePersistence;
        $this->scheduleChecker = $scheduleChecker;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws NoSuchEntityException
     */
    protected function performValidation($profile, $action)
    {
        if ($profile->getStatus() == StatusSource::ACTIVE) {
            $schedule = $this->schedulePersistence->getByProfile($profile->getProfileId());
            if (!$this->scheduleChecker->isLastNextPayment($schedule)
                && !$this->scheduleChecker->isMembershipNextPayment($schedule)
            ) {
                $this->addMessages(['The Extend action is not possible for this subscription.']);
            }
        }
    }
}
