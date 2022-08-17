<?php
namespace Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend;

use Aheadworks\Sarp2\Api\Data\ProfileInterface;
use Aheadworks\Sarp2\Api\ProfileRepositoryInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Generator\Source;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceFactory;
use Aheadworks\Sarp2\Engine\Payment\Generator\Type\NextAfterExtend;
use Aheadworks\Sarp2\Engine\Payment\PaymentsList;
use Aheadworks\Sarp2\Engine\Payment\Persistence;
use Aheadworks\Sarp2\Engine\Payment\Schedule\Checker;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\ApplierInterface;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ResultFactory;
use Aheadworks\Sarp2\Engine\Profile\Action\Validation\ValidatorComposite;
use Aheadworks\Sarp2\Engine\Profile\ActionInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Applier
 *
 * @package Aheadworks\Sarp2\Engine\Profile\Action\Type\Extend
 */
class Applier implements ApplierInterface
{
    /**
     * @var ResultFactory
     */
    private $validationResultFactory;

    /**
     * @var ValidatorComposite
     */
    private $validator;

    /**
     * @var Persistence
     */
    private $paymentPersistence;

    /**
     * @var Payment\Schedule\Persistence
     */
    private $schedulePersistence;

    /**
     * @var ProfileRepositoryInterface
     */
    private $profileRepository;

    /**
     * @var SourceFactory
     */
    private $generatorSourceFactory;

    /**
     * @var NextAfterExtend
     */
    private $generator;

    /**
     * @var Checker
     */
    private $scheduleChecker;

    /**
     * @var PaymentsList
     */
    private $paymentsList;

    /**
     * @param ResultFactory $validationResultFactory
     * @param ValidatorComposite $validator
     * @param Persistence $paymentPersistence
     * @param Payment\Schedule\Persistence $schedulePersistence
     * @param ProfileRepositoryInterface $profileRepository
     * @param SourceFactory $generatorSourceFactory
     * @param NextAfterExtend $generator
     * @param Checker $scheduleChecker
     * @param PaymentsList $paymentsList
     */
    public function __construct(
        ResultFactory $validationResultFactory,
        ValidatorComposite $validator,
        Persistence $paymentPersistence,
        Payment\Schedule\Persistence $schedulePersistence,
        ProfileRepositoryInterface $profileRepository,
        SourceFactory $generatorSourceFactory,
        NextAfterExtend $generator,
        Checker $scheduleChecker,
        PaymentsList $paymentsList
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->validator = $validator;
        $this->paymentPersistence = $paymentPersistence;
        $this->schedulePersistence = $schedulePersistence;
        $this->profileRepository = $profileRepository;
        $this->generatorSourceFactory = $generatorSourceFactory;
        $this->generator = $generator;
        $this->scheduleChecker = $scheduleChecker;
        $this->paymentsList = $paymentsList;
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function apply(ProfileInterface $profile, ActionInterface $action)
    {
        //Was added for recalculate profile totals when last order was with one-off item
        $this->profileRepository->save($profile, true);
        $schedule = $this->schedulePersistence->getByProfile($profile->getProfileId());
        $wasStatus = $profile->getStatus();
        $profileDefinition = $profile->getProfileDefinition();
        $isLastPeriodHolder = $this->scheduleChecker->isMembershipNextPayment($schedule);

        $regularTotalCount = $schedule->getRegularTotalCount() + $profileDefinition->getTotalBillingCycles();
        $membershipTotalCount = $schedule->getMembershipTotalCount();
        if ($profileDefinition->getIsMembershipModelEnabled()
            && ($wasStatus == Status::EXPIRED)
        ) {
            $membershipTotalCount += 1;
        }

        $profile->setStatus(Status::ACTIVE);
        $schedule
            ->setRegularTotalCount($regularTotalCount)
            ->setMembershipTotalCount($membershipTotalCount);
        if ($wasStatus != Status::ACTIVE) {
            $schedule->setIsReactivated(true);
        }

        $this->schedulePersistence->save($schedule);

        if ($wasStatus == Status::EXPIRED
            || $isLastPeriodHolder
        ) {
            $this->generateNextPlanetPayments($profile);

            if ($isLastPeriodHolder) {
                $this->clearLastPeriodHolderPayment($profile);
            }
        }
    }

    /**
     * Generate new Payments
     *
     * @param ProfileInterface $profile
     * @throws CouldNotSaveException
     */
    private function generateNextPlanetPayments($profile)
    {
        /** @var Source $source */
        $source = $this->generatorSourceFactory->create([
            'profile' => $profile
        ]);
        $nextPlanetPayments = $this->generator->generate($source);

        /** @var Payment $nextPlanetPayment */
        foreach ($nextPlanetPayments as $nextPlanetPayment) {
            if ($nextPlanetPayment->isBundled()) {
                $this->paymentPersistence->massSave(
                    array_merge([$nextPlanetPayment], $nextPlanetPayment->getChildItems())
                );
            } else {
                $this->paymentPersistence->save($nextPlanetPayment);
            }
        }
    }

    /**
     * Delete last period holder payments
     *
     * @param ProfileInterface $profile
     */
    private function clearLastPeriodHolderPayment($profile)
    {
        $payments = $this->paymentsList->getLastScheduled($profile->getProfileId());
        foreach ($payments as $payment) {
            if ($payment->getType() == PaymentInterface::TYPE_LAST_PERIOD_HOLDER) {
                try {
                    $this->paymentPersistence->delete($payment);
                } catch (CouldNotDeleteException $exception) {
                }
            }
        }
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
