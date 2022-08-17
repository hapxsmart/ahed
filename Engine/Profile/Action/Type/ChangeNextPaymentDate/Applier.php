<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Notification\Manager;
use Aheadworks\Sarp2\Engine\Notification\Offer\Extend\Processor as OfferNotificationManager;
use Aheadworks\Sarp2\Engine\NotificationInterface;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker as ScheduleChecker;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Persistence as SchedulePersistence;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Applier
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\ChangeNextPaymentDate
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

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
     * @var OfferNotificationManager
     */
    private $offerNotificationManager;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @var SchedulePersistence
     */
    private $schedulePersistence;

    /**
     * @var ScheduleChecker
     */
    private $scheduleChecker;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @param ResultFactory $validationResultFactory
     * @param PaymentsList $paymentsList
     * @param Persistence $paymentPersistence
     * @param Manager $notificationManager
     * @param OfferNotificationManager $offerNotificationManager
     * @param ValidatorComposite $validator
     * @param SchedulePersistence $schedulePersistence
     * @param ScheduleChecker $scheduleChecker
     * @param ProfileRepositoryInterface $profileRepository
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        PaymentsList $paymentsList,
        Persistence $paymentPersistence,
        Manager $notificationManager,
        OfferNotificationManager $offerNotificationManager,
        ValidatorComposite $validator,
        SchedulePersistence $schedulePersistence,
        ScheduleChecker $scheduleChecker,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->paymentsList = $paymentsList;
        $this->paymentPersistence = $paymentPersistence;
        $this->notificationManager = $notificationManager;
        $this->offerNotificationManager = $offerNotificationManager;
        $this->validator = $validator;
        $this->schedulePersistence = $schedulePersistence;
        $this->scheduleChecker = $scheduleChecker;
        $this->profileRepository = $profileRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        $newNextPaymentDate = $action->getData()->getNewNextPaymentDate();

        $this->changeProfileStartDateIfNoPaidPayments($profile, $newNextPaymentDate);

        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            $payment->setScheduledAt($newNextPaymentDate);
        }
        if (count($payments)) {
            $this->paymentPersistence->massSave($payments);
            $this->notificationManager->reschedule(NotificationInterface::TYPE_UPCOMING_BILLING, $payments);
            $this->offerNotificationManager->rescheduleNotification($profile);
        }
    }

    /**
     * Change startDate in profile if there were no payments
     * @see https://aheadworks.atlassian.net/browse/M2SARP2-1181
     *
     * @param ProfileInterface $profile
     * @param $newNextPaymentDate
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function changeProfileStartDateIfNoPaidPayments(ProfileInterface $profile, $newNextPaymentDate)
    {
        $schedule = $this->getSchedule($profile->getProfileId());
        if ($schedule && $this->scheduleChecker->isNoPayments($schedule)) {
            $profile->setStartDate($newNextPaymentDate);
            $this->profileRepository->save($profile, false);
        }
    }

    /**
     * Retrieve schedule by profile id
     *
     * @param $profileId
     * @return \Aheadworks\Sarp2\Engine\Payment\Schedule|null
     */
    private function getSchedule($profileId)
    {
        $schedule = null;
        try {
            $schedule = $this->schedulePersistence->getByProfile($profileId);
        } catch (NoSuchEntityException $exception) {
        }

        return $schedule;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ProfileInterface $profile, ActionInterface $action)
    {
        $isValid = $this->validator->isValid($profile, $action);

        $resultData = ['isValid' => $isValid];
        if (!$isValid) {
            $resultData['message'] = $this->validator->getMessage();
        }
        return $this->validationResultFactory->create($resultData);
    }
}
