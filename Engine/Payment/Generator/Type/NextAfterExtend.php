<?php
namespace Aheadworks\Sarp2\Engine\Payment\Generator\Type;

use Aheadworks\Sarp2\Engine\DataResolver\NextPaymentDate;
use Aheadworks\Sarp2\Engine\Payment;
use Aheadworks\Sarp2\Engine\Payment\Generator\Evaluation;
use Aheadworks\Sarp2\Engine\Payment\Generator\SourceInterface;
use Aheadworks\Sarp2\Engine\Payment\GeneratorInterface;
use Aheadworks\Sarp2\Engine\Payment\Schedule;
use Aheadworks\Sarp2\Engine\Payment\ScheduleFactory;
use Aheadworks\Sarp2\Engine\PaymentFactory;
use Aheadworks\Sarp2\Engine\PaymentInterface;
use Aheadworks\Sarp2\Model\Profile\Source\Status;

/**
 * Class NextAfterExtend
 *
 * @package Aheadworks\Sarp2\Engine\Payment\Generator\Type
 */
class NextAfterExtend implements GeneratorInterface
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
     * @var Schedule\Persistence
     */
    private $schedulePersistence;

    /**
     * @param Evaluation $evaluation
     * @param NextPaymentDate $nextPaymentDate
     * @param PaymentFactory $paymentFactory
     * @param Schedule\Persistence $schedulePersistence
     */
    public function __construct(
        Evaluation $evaluation,
        NextPaymentDate $nextPaymentDate,
        PaymentFactory $paymentFactory,
        Schedule\Persistence $schedulePersistence
    ) {
        $this->evaluation = $evaluation;
        $this->nextPaymentDate = $nextPaymentDate;
        $this->paymentFactory = $paymentFactory;
        $this->schedulePersistence = $schedulePersistence;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(SourceInterface $source)
    {
        $nextPayments = [];
        $profile = $source->getProfile();
        if ($profile) {
            if ($profile->getStatus() != Status::EXPIRED) {
                $schedule = $this->schedulePersistence->getByProfile($profile->getProfileId());
                $paidAt = $profile->getLastOrderDate();
                $nextPaymentDate = $this->nextPaymentDate->getDateNext(
                    $paidAt,
                    $schedule->getPeriod(),
                    $schedule->getFrequency()
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
