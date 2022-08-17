<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class DefaultStatus
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type
 */
class DefaultStatus implements StatusApplierInterface
{
    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     */
    public function __construct(
        PaymentsList $paymentsList,
        Persistence $paymentPersistence
    ) {
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->getSchedule()->setIsReactivated($status == Status::ACTIVE);
        }
        $profile->setStatus($status);
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
        }
    }
}
