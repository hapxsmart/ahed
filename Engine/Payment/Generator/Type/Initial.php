<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Api\Data\PlanDefinitionInterface;
use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\GeneratorInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Class Initial
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type
 */
class Initial implements GeneratorInterface
{
    /**
     * @var Evaluation
     */
    private $evaluation;

    /**
     * @var NextPaymentDate
     */
    private $nextPaymentDate;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var ScheduleFactory
     */
    private $scheduleFactory;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ValueResolver
     */
    private $schedulePeriodValueResolver;

    /**
     * @param Evaluation $evaluation
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentFactory $paymentFactory
     * @param ScheduleFactory $scheduleFactory
     * @param DateTime $dateTime
     * @param ValueResolver $schedulePeriodValueResolver
     */
    public function __construct(
        Evaluation $evaluation,
        NextPaymentDate $nextPaymentDate,
        PaymentFactory $paymentFactory,
        ScheduleFactory $scheduleFactory,
        DateTime $dateTime,
        ValueResolver $schedulePeriodValueResolver
    ) {
        $this->evaluation = $evaluation;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->paymentFactory = $paymentFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->dateTime = $dateTime;
        $this->schedulePeriodValueResolver = $schedulePeriodValueResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $payments = [];
        $profile = $source->getProfile();
        if ($profile) {
            /** @var Schedule $schedule */
            $schedule = $this->scheduleFactory->create();
            $planDefinition = $profile->getProfileDefinition();
            $membershipTotalCount = (int)(
                $planDefinition->getIsMembershipModelEnabled() && $planDefinition->getTotalBillingCycles()
            );
            $schedule
                ->setPeriod($planDefinition->getBillingPeriod())
                ->setFrequency($planDefinition->getBillingFrequency())
                ->setTrialPeriod($planDefinition->getTrialBillingPeriod())
                ->setTrialFrequency($planDefinition->getTrialBillingFrequency())
                ->setTrialTotalCount(
                    $planDefinition->getIsTrialPeriodEnabled()
                        ? $planDefinition->getTrialTotalBillingCycles()
                        : 0
                )
                ->setRegularTotalCount($planDefinition->getTotalBillingCycles())
                ->setStoreId($profile->getStoreId())
                ->setIsMembershipModel(
                    $planDefinition->getIsMembershipModelEnabled()
                        ? $planDefinition->getIsMembershipModelEnabled()
                        : false
                )
                ->setMembershipTotalCount($membershipTotalCount);

            $prePaymentInfo = $profile->getPrePaymentInfo();
            $isInitialPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsInitialFeePaid()
                : false;
            $isTrialPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsTrialPaid()
                : false;
            $isRegularPaid = $prePaymentInfo
                ? $prePaymentInfo->getIsRegularPaid()
                : false;

            $today = $this->dateTime->formatDate(true);
            $wasPrePayments = $isInitialPaid || $isTrialPaid || $isRegularPaid;
            if ($wasPrePayments) {
                $schedule->setIsInitialPaid($isInitialPaid)
                    ->setTrialCount($isTrialPaid ? 1 : 0)
                    ->setRegularCount($isRegularPaid ? 1 : 0);

                $nextPaymentDate = $this->nextPaymentDate->getDateNext(
                    $today,
                    $this->schedulePeriodValueResolver->getPeriod($schedule),
                    $this->schedulePeriodValueResolver->getFrequency($schedule)
                );
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $nextPaymentDate,
                    $today
                );
            } else {
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $today
                );
            }

            foreach ($paymentsDetails as $details) {
                /** @var Payment $payment */
                $payment = $this->paymentFactory->create();
                $payment->setProfileId($profile->getProfileId())
                    ->setProfile($profile)
                    ->setType($details->getPaymentType())
                    ->setPaymentPeriod($details->getPaymentPeriod())
                    ->setPaymentStatus(PaymentInterface::STATUS_PLANNED)
                    ->setScheduledAt($details->getDate())
                    ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
                    ->setTotalScheduled($details->getTotalAmount())
                    ->setBaseTotalScheduled($details->getBaseTotalAmount())
                    ->setSchedule($schedule);
                $payments[] = $payment;
            }
        }
        return $payments;
    }
}
