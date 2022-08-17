<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Validator;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Model\Profile;
use Aheadworks\Sarp2\Model\Profile\Source\Status as StatusSource;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\AbstractValidator;

/**
 * Class Payment
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Validator
 */
class Payment extends AbstractValidator
{
    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @param PaymentsList $paymentsList
     */
    public function __construct(
        PaymentsList $paymentsList
    ) {
        $this->paymentsList = $paymentsList;
    }

    /**
     * @inheritDoc
     */
    protected function performValidation($profile, $action)
    {
        $newStatus = $action->getData()->getStatus();
        $currentStatus = $this->getProfileStatus($profile);

        if ($newStatus == StatusSource::ACTIVE && $currentStatus == StatusSource::SUSPENDED) {
            $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
            foreach ($payments as $payment) {
                if ($payment->getType() == PaymentInterface::TYPE_REATTEMPT) {
                    $this->addMessages([
                        'Unable to perform activation action, subscription suspended due to payment failures.'
                    ]);
                    break;
                }
            }
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
