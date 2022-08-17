<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\Type;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeStatus\Status\StatusApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\ResourceModel\Engine\Profile as ProfileResource;
use Magento\Framework\Exception\CouldNotSaveException;

class Cancelled implements StatusApplierInterface
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
     * @var Manager
     */
    private $notificationManager;

    /**
     * @var ProfileResource
     */
    private $profileResource;

    /**
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     * @param ProfileResource $profileResource
     */
    public function __construct(
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager,
        ProfileResource $profileResource
    ) {
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
        $this->profileResource = $profileResource;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $status = $action->getData()->getStatus();
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());

        if ($profile->getProfileDefinition()->getIsMembershipModelEnabled()) {
            foreach ($payments as $payment) {
                $payment->setPaymentStatus(PaymentInterface::STATUS_CANCELLED);
            }
        } else {
            $profile->setStatus($status);
        }

        foreach ($payments as $payment) {
            $payment->getSchedule()->setIsReactivated(false);
        }

        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);

            $payment = reset($payments);
            $this->notificationManager->schedule(
                NotificationInterface::TYPE_CANCELLED_SUBSCRIPTION,
                $payment
            );
        }
    }
}
