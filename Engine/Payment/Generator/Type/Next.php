<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment\GeneratorInterface;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule\ValueResolver;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Model\DateTime\Modifier as DateTimeModifier;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

class Next implements GeneratorInterface
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
     * @var ValueResolver
     */
    private $schedulePeriodValueResolver;

    /**
     * @var DateTimeModifier
     */
    private $dateTimeModifier;

    /**
     * @param Evaluation $evaluation
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentFactory $paymentFactory
     * @param ValueResolver $periodValueResolver
     * @param DateTimeModifier $dateTimeModifier
     */
    public function __construct(
        Evaluation $evaluation,
        NextPaymentDate $nextPaymentDate,
        PaymentFactory $paymentFactory,
        ValueResolver $periodValueResolver,
        DateTimeModifier $dateTimeModifier
    ) {
        $this->evaluation = $evaluation;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->paymentFactory = $paymentFactory;
        $this->schedulePeriodValueResolver = $periodValueResolver;
        $this->dateTimeModifier = $dateTimeModifier;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $nextPayments = [];
        foreach ($source->getPayments() as $payment) {
            $profile = $payment->getProfile(true);
            if (!$profile) {
                continue;
            }
            if ($profile->getStatus() != Status::EXPIRED) {
                $schedule = $payment->getSchedule();
                $paidAt = $payment->getPaidAt();
                $paidAtWithShiftedTime = $this->dateTimeModifier->copyTime($payment->getScheduledAt(), $paidAt);
                $nextPaymentDate = $this->nextPaymentDate->getDateNext(
                    $paidAtWithShiftedTime,
                    $this->schedulePeriodValueResolver->getPeriod($schedule),
                    $this->schedulePeriodValueResolver->getFrequency($schedule)
                );
                $paymentsDetails = $this->evaluation->evaluate(
                    $schedule,
                    $profile,
                    $nextPaymentDate,
                    $paidAt
                );
                foreach ($paymentsDetails as $details) {
                    /** @var Payment $nextPayment */
                    $nextPayment = $this->paymentFactory->create();
                    $nextPayment->setProfileId($profile->getProfileId())
                        ->setProfile($profile)
                        ->setType($details->getPaymentType())
                        ->setPaymentPeriod($details->getPaymentPeriod())
                        ->setPaymentStatus(PaymentInterface::STATUS_PLANNED)
                        ->setScheduledAt($details->getDate())
                        ->setPaymentData(['token_id' => $profile->getPaymentTokenId()])
                        ->setTotalScheduled($details->getTotalAmount())
                        ->setBaseTotalScheduled($details->getBaseTotalAmount())
                        ->setSchedule($schedule)
                        ->setScheduleId($schedule->getScheduleId());
                    $nextPayments[] = $nextPayment;
                }
            }
        }
        return $nextPayments;
    }
}
